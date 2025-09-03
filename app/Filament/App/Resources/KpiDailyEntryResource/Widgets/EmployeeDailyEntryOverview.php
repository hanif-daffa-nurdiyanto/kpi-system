<?php

namespace App\Filament\App\Resources\KpiDailyEntryResource\Widgets;

use App\Models\KpiDailyEntry;
use App\Models\KpiMetric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EmployeeDailyEntryOverview extends BaseWidget
{
    protected int $cacheMinutes = 5;
    protected ?Collection $departmentUserIds = null;

    protected function getStats(): array
    {
        try {
            return [
                $this->getGroupedMatrixValues(),
                $this->getWeeklyEntriesStat(),
                $this->getMonthlyEntriesStat(),
            ];
        } catch (\Exception $e) {
            Log::error('Error in EmployeeDailyEntryOverview::getStats: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                Stat::make('Error', 'Unable to load data')
                    ->description('Please try refreshing the page')
                    ->color('danger')
            ];
        }
    }

    /**
     * Get weekly entries stat with chart
     */
    protected function getWeeklyEntriesStat(): Stat
    {
        $totalWeekly = $this->getTotalWeeklyEntries();
        $chartWeekly = $this->getWeeklyChart();

        return Stat::make('Weekly Entries', $totalWeekly)
            ->description($this->getDescriptionByRole('weekly'))
            ->descriptionIcon('heroicon-m-calendar-days')
            ->color('success')
            ->chart($chartWeekly);
    }

    /**
     * Get monthly entries stat with chart
     */
    protected function getMonthlyEntriesStat(): Stat
    {
        $totalMonthly = $this->getTotalMonthlyEntries();
        $chartMonthly = $this->getMonthlyChart();

        return Stat::make('Monthly Entries', $totalMonthly)
            ->description($this->getDescriptionByRole('monthly'))
            ->descriptionIcon('heroicon-m-calendar')
            ->color('warning')
            ->chart($chartMonthly);
    }

    /**
     * Generate weekly chart data with caching
     */
    protected function getWeeklyChart(): array
    {
        $cacheKey = $this->getCacheKey('weekly_chart');

        return Cache::remember($cacheKey, $this->cacheMinutes, function () {
            $data = collect(range(0, 11))->map(function ($weeksAgo) {
                $startOfWeek = Carbon::now()->subWeeks($weeksAgo)->startOfWeek();
                $endOfWeek = Carbon::now()->subWeeks($weeksAgo)->endOfWeek();

                return $this->getKpiQueryBuilder()
                    ->whereBetween('entry_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                    ->where('status', 'approved')
                    ->count();
            });

            return $data->reverse()->values()->toArray();
        });
    }

    /**
     * Generate monthly chart data with caching
     */
    protected function getMonthlyChart(): array
    {
        $cacheKey = $this->getCacheKey('monthly_chart');

        return Cache::remember($cacheKey, $this->cacheMinutes, function () {
            $data = collect(range(0, 11))->map(function ($monthsAgo) {
                $date = Carbon::now()->subMonths($monthsAgo);

                return $this->getKpiQueryBuilder()
                    ->whereMonth('entry_date', $date->month)
                    ->whereYear('entry_date', $date->year)
                    ->where('status', 'approved')
                    ->count();
            });

            return $data->reverse()->values()->toArray();
        });
    }

    /**
     * Get base KPI query builder with proper user filtering
     */
    protected function getKpiQueryBuilder(): Builder
    {
        $query = KpiDailyEntry::query();
        $userIds = $this->getFilteredUserIds();

        if (count($userIds) === 1) {
            $query->where('user_id', $userIds[0]);
        } else {
            $query->whereIn('user_id', $userIds);
        }

        return $query;
    }

    /**
     * Get filtered user IDs based on role with caching
     */
    protected function getFilteredUserIds(): array
    {
        if ($this->departmentUserIds !== null) {
            return $this->departmentUserIds->toArray();
        }

        $user = auth()->user();
        $cacheKey = $this->getCacheKey('user_ids');

        $userIds = Cache::remember($cacheKey, $this->cacheMinutes, function () use ($user) {
            if ($user->hasRole('manager')) {
                return $this->getDepartmentUserIds($user);
            }

            return [$user->id];
        });

        $this->departmentUserIds = collect($userIds);

        return $userIds;
    }

    /**
     * Get department user IDs for manager with error handling
     */
    protected function getDepartmentUserIds($manager): array
    {
        try {
            $department = $manager->department;

            if (!$department) {
                return [$manager->id];
            }

            $employeeUserIds = $department->employees()
                ->whereHas('user')
                ->with('user:id')
                ->get()
                ->pluck('user_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
            if (!in_array($manager->id, $employeeUserIds)) {
                $employeeUserIds[] = $manager->id;
            }

            return $employeeUserIds;

        } catch (\Exception $e) {
            Log::warning('Error getting department user IDs: ' . $e->getMessage(), [
                'manager_id' => $manager->id
            ]);

            return [$manager->id];
        }
    }

    /**
     * Get today's grouped matrix values with improved error handling and better UI
     */
    public function getGroupedMatrixValues(): Stat
    {
        try {
            $cacheKey = $this->getCacheKey('daily_matrix');

            $data = Cache::remember($cacheKey, $this->cacheMinutes, function () {
                return $this->fetchGroupedMatrixData();
            });
            $description = $this->formatMatrixDescription($data['grouped_values'], $data['total_daily']);
            $color = $data['total_daily'] > 0 ? 'primary' : 'gray';
            return Stat::make('Daily Entries', $data['total_daily'])
                ->description($description)
                ->color($color);

        } catch (\Exception $e) {
            Log::error('Error in getGroupedMatrixValues: ' . $e->getMessage());

            return Stat::make('Daily Entries', 0)
                ->description('Unable to load today\'s data')
                ->color('danger');
        }
    }

    /**
     * Fetch grouped matrix data with database-agnostic approach
     */
    protected function fetchGroupedMatrixData(): array
    {
        $userIds = $this->getFilteredUserIds();
        $today = Carbon::today()->format('Y-m-d');
        $groupedValues = collect();
        $totalDaily = 0;

        try {
            $subQuery = DB::table('kpi_daily_entries')
                ->whereIn('user_id', $userIds)
                ->where('status', 'approved')
                ->whereDate('entry_date', $today)
                ->select('id');

            $groupedValues = DB::table('kpi_entry_details as ked')
                ->joinSub($subQuery, 'kde', function ($join) {
                    $join->on('ked.entry_id', '=', 'kde.id');
                })
                ->join('kpi_metrics as km', 'ked.metric_id', '=', 'km.id')
                ->select([
                    'ked.metric_id',
                    'km.name as metric_name',
                    DB::raw('CAST(SUM(ked.value) AS DECIMAL(15,2)) as total_value')
                ])
                ->groupBy(['ked.metric_id', 'km.name'])
                ->orderBy('km.name')
                ->get();
            $totalDaily = $this->getKpiQueryBuilder()
                ->whereDate('entry_date', $today)
                ->where('status', 'approved')
                ->count();

        } catch (\Exception $e) {
            Log::error('Error fetching grouped matrix data: ' . $e->getMessage());
            $totalDaily = $this->getKpiQueryBuilder()
                ->whereDate('entry_date', $today)
                ->where('status', 'approved')
                ->count();
        }

        return [
            'grouped_values' => $groupedValues,
            'total_daily' => $totalDaily
        ];
    }

    /**
     * Format matrix description with clean layout and proper spacing
     */
    protected function formatMatrixDescription(Collection $groupedValues, int $totalDaily): HtmlString
    {
        if ($totalDaily === 0) {
            return new HtmlString('No approved entries for today');
        }

        if ($groupedValues->isEmpty()) {
            return new HtmlString($totalDaily . ' entries submitted');
        }

        $formatted = $groupedValues->map(function ($item) {
            $name = e($item->metric_name ?? 'Unknown Metric');
            $value = number_format((float)($item->total_value ?? 0), 2);

            return "<div class=\"flex justify-between items-center\">
                        <span class=\"text-gray-600\">{$name}:</span>
                        <span class=\"font-semibold text-primary-600\">{$value}</span>
                    </div>";
        })->take(3); // Limit to 3 items for cleaner display

        $html = '<div class="space-y-1">' . $formatted->implode('') . '</div>';

        // Show remaining count if there are more metrics
        if ($groupedValues->count() > 3) {
            $remaining = $groupedValues->count() - 3;
            $html .= "<div class=\"text-xs text-gray-500 mt-1 text-center\">
                        + {$remaining} more
                      </div>";
        }

        return new HtmlString($html);
    }

    /**
     * Get total weekly entries with error handling
     */
    public function getTotalWeeklyEntries(): int
    {
        try {
            $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
            $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');

            return $this->getKpiQueryBuilder()
                ->whereBetween('entry_date', [$startOfWeek, $endOfWeek])
                ->where('status', 'approved')
                ->count();
        } catch (\Exception $e) {
            Log::error('Error getting weekly entries: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get total monthly entries with error handling
     */
    public function getTotalMonthlyEntries(): int
    {
        try {
            $now = Carbon::now();

            return $this->getKpiQueryBuilder()
                ->whereMonth('entry_date', $now->month)
                ->whereYear('entry_date', $now->year)
                ->where('status', 'approved')
                ->count();
        } catch (\Exception $e) {
            Log::error('Error getting monthly entries: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get total daily entries
     */
    public function getTotalDailyEntries(): int
    {
        try {
            return $this->getKpiQueryBuilder()
                ->whereDate('entry_date', Carbon::today())
                ->count();
        } catch (\Exception $e) {
            Log::error('Error getting daily entries: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get description based on user role with better messaging
     */
    protected function getDescriptionByRole(string $period): string
    {
        $user = auth()->user();

        if ($user->hasRole('manager')) {
            return "Approved {$period} entries from your department";
        }

        return "Your approved {$period} entries";
    }

    /**
     * Generate cache key for the current user and context
     */
    protected function getCacheKey(string $type): string
    {
        $user = auth()->user();
        $role = $user->hasRole('manager') ? 'manager' : 'employee';
        $userId = $user->id;
        $date = Carbon::now()->format('Y-m-d');

        return "kpi_widget_{$type}_{$role}_{$userId}_{$date}";
    }

    /**
     * Clear all related cache when data changes
     */
    public function clearCache(): void
    {
        $types = ['weekly_chart', 'monthly_chart', 'daily_matrix', 'user_ids'];

        foreach ($types as $type) {
            Cache::forget($this->getCacheKey($type));
        }
    }

    /**
     * Get department user IDs by employee (if needed for future use)
     */
    protected function getDepartmentUserIdsByEmployee($user): array
    {
        try {
            $employee = $user->employee;

            if (!$employee || !$employee->department) {
                return [$user->id];
            }

            $employeeUserIds = $employee->department->employees()
                ->whereHas('user')
                ->with('user:id')
                ->get()
                ->pluck('user_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            return $employeeUserIds ?: [$user->id];

        } catch (\Exception $e) {
            Log::warning('Error getting department user IDs by employee: ' . $e->getMessage());
            return [$user->id];
        }
    }
}