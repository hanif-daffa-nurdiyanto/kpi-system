<?php

namespace App\Services;

use App\Models\KpiEntryDetail;
use App\Models\TeamGoal;
use App\Models\TeamGoals;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TeamGoalService
{
    /**
     * Get complete performance data for a team goal
     */
    public function getPerformanceData(TeamGoals $teamGoal): array
    {
        $now = Carbon::now();
        $startDate = Carbon::parse($teamGoal->start_date);
        $endDate = Carbon::parse($teamGoal->end_date);
        $metricId = $teamGoal->metric_id;
        $departmentId = $teamGoal->department_id;
        $isHigherBetter = $teamGoal->metric->is_higher_better;

        // Calculate progress in days
        $totalDays = $startDate->diffInDays($endDate);
        $passedDays = $startDate->diffInDays($now);
        $progressPercentage = min(100, max(0, ($passedDays / max(1, $totalDays)) * 100));

        // Get metric entries
        $metricEntries = $this->getMetricEntries($departmentId, $metricId, $startDate, $endDate);
        $currentValue = $metricEntries->avg('value') ?? 0;

        // Calculate target achievement percentage
        $targetValue = $teamGoal->target_value;
        $achievementPercentage = $this->calculateAchievementPercentage(
            $currentValue,
            $targetValue,
            $isHigherBetter
        );

        // Get department employees count
        $employeesCount = $teamGoal->department->employees()->count();

        // Get performance trend data
        $trendData = $this->getTrendData($departmentId, $metricId);

        // Status calculation
        list($status, $statusColor) = $this->calculateStatus($achievementPercentage);

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'currentValue' => $currentValue,
            'targetValue' => $targetValue,
            'achievementPercentage' => $achievementPercentage,
            'progressPercentage' => $progressPercentage,
            'employeesCount' => $employeesCount,
            'trendData' => $trendData,
            'status' => $status,
            'statusColor' => $statusColor,
            'isHigherBetter' => $isHigherBetter,
            'metricEntries' => $metricEntries,
            'totalDays' => $totalDays,
            'passedDays' => $passedDays,
            'metricId' => $metricId,
            'departmentId' => $departmentId,
            'unit' => $teamGoal->metric->unit
        ];
    }

    /**
     * Get metric entries for a specific department and time period
     */
    public function getMetricEntries(int $departmentId, int $metricId, Carbon $startDate, Carbon $endDate): Collection
    {
        return KpiEntryDetail::whereHas('kpiDailyEntry', function (Builder $query) use ($departmentId, $startDate, $endDate) {
            $query->whereHas('user', function (Builder $userQuery) use ($departmentId) {
                $userQuery->whereHas('employee', function (Builder $employeeQuery) use ($departmentId) {
                    $employeeQuery->where('department_id', $departmentId);
                });
            })
                ->whereBetween('entry_date', [$startDate, $endDate]);
        })
            ->where('metric_id', $metricId)
            ->get();
    }

    /**
     * Calculate achievement percentage based on current and target values
     */
    public function calculateAchievementPercentage(float $currentValue, float $targetValue, bool $isHigherBetter): float
    {
        if ($isHigherBetter) {
            return min(100, ($targetValue > 0) ? ($currentValue / $targetValue) * 100 : 0);
        } else {
            return min(100, ($currentValue > 0) ? ($targetValue / $currentValue) * 100 : 100);
        }
    }

    /**
     * Get trend data for the last 7 days
     */
    public function getTrendData(int $departmentId, int $metricId): array
    {
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayEntries = KpiEntryDetail::whereHas('kpiDailyEntry', function (Builder $query) use ($departmentId, $date) {
                $query->whereHas('user', function (Builder $userQuery) use ($departmentId) {
                    $userQuery->whereHas('employee', function (Builder $employeeQuery) use ($departmentId) {
                        $employeeQuery->where('department_id', $departmentId);
                    });
                })
                    ->whereDate('entry_date', $date);
            })
                ->where('metric_id', $metricId)
                ->get();

            $trendData[$date->format('d/m')] = $dayEntries->avg('value') ?? 0;
        }

        // Format trend for display
        $trendDataFormatted = collect($trendData)->map(function ($value, $key) {
            return "{$key}: {$value}";
        })->implode(' | ');

        return [
            'raw' => $trendData,
            'formatted' => $trendDataFormatted
        ];
    }

    /**
     * Calculate status and color based on achievement percentage
     */
    public function calculateStatus(float $achievementPercentage): array
    {
        $status = 'On Track';
        $statusColor = 'success';

        if ($achievementPercentage < 80) {
            $status = 'At Risk';
            $statusColor = 'warning';
        }
        if ($achievementPercentage < 50) {
            $status = 'Off Track';
            $statusColor = 'danger';
        }

        return [$status, $statusColor];
    }

    /**
     * Get historical performance data (last 6 months)
     */
    public function getHistoricalPerformanceData(int $metricId, int $departmentId, string $unit): string
    {
        $history = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();

            $monthlyAvg = KpiEntryDetail::whereHas('kpiDailyEntry', function (Builder $query) use ($departmentId, $monthStart, $monthEnd) {
                $query->whereHas('user', function (Builder $userQuery) use ($departmentId) {
                    $userQuery->whereHas('employee', function (Builder $employeeQuery) use ($departmentId) {
                        $employeeQuery->where('department_id', $departmentId);
                    });
                })
                    ->whereBetween('entry_date', [$monthStart, $monthEnd]);
            })
                ->where('metric_id', $metricId)
                ->get()
                ->avg('value') ?? 0;

            $history[$monthStart->format('M Y')] = $monthlyAvg;
        }

        return collect($history)->map(function ($value, $key) use ($unit) {
            return "**{$key}**: " . number_format($value, 2) . " {$unit}";
        })->implode("\n\n");
    }

    /**
     * Generate recommendations based on performance data
     */
    public function getRecommendations(float $achievementPercentage, bool $isHigherBetter, float $currentValue, float $targetValue, string $unit): string
    {
        $recommendations = [];

        if ($achievementPercentage < 70) {
            $recommendations[] = "**Action Required**: Performance is significantly below target.";

            if ($isHigherBetter) {
                $gap = $targetValue - $currentValue;
                $recommendations[] = "Current gap: {$gap} {$unit} needed to reach target.";
                $recommendations[] = "Consider reviewing department processes to identify improvement opportunities.";
            } else {
                $excess = $currentValue - $targetValue;
                $recommendations[] = "Current excess: {$excess} {$unit} above optimal level.";
                $recommendations[] = "Consider optimizing resource allocation or process efficiency.";
            }
        } elseif ($achievementPercentage < 90) {
            $recommendations[] = "**Improvement Needed**: Performance is approaching target but still requires attention.";

            if ($isHigherBetter) {
                $gap = $targetValue - $currentValue;
                $recommendations[] = "Current gap: {$gap} {$unit} needed to reach target.";
                $recommendations[] = "Consider targeted coaching or incremental process improvements.";
            } else {
                $excess = $currentValue - $targetValue;
                $recommendations[] = "Current excess: {$excess} {$unit} above optimal level.";
                $recommendations[] = "Continue refining processes for better efficiency.";
            }
        } else {
            $recommendations[] = "**Great Performance**: Target is being met or exceeded.";
            $recommendations[] = "Consider documenting successful practices and sharing with other departments.";

            if ($achievementPercentage > 120) {
                $recommendations[] = "Current target may be too low. Consider reviewing for next period.";
            }
        }

        return implode("\n\n", $recommendations);
    }

    /**
     * Toggle team goal active status
     */
    public function toggleActiveStatus(TeamGoals $teamGoal): bool
    {
        $teamGoal->update([
            'is_active' => !$teamGoal->is_active
        ]);

        return $teamGoal->is_active;
    }
}
