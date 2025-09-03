<?php

namespace App\Services;

use App\Models\KpiDailyEntry;
use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KpiStatsService
{
    /**
     * Get comprehensive admin overview stats
     */
    public function getAdminOverviewStats(
        ?int $departmentId = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        return [
            'main_stats' => $this->getMainStats($departmentId, $startDate, $endDate),
            'performance_metrics' => $this->getPerformanceMetrics($departmentId, $startDate, $endDate),
            'trend_data' => $this->getTrendData($departmentId, $startDate, $endDate),
            'department_breakdown' => $this->getDepartmentBreakdown($startDate, $endDate),
            'status_distribution' => $this->getStatusDistribution($departmentId, $startDate, $endDate),
            'top_performers' => $this->getTopPerformers($departmentId, $startDate, $endDate),
            'recent_activity' => $this->getRecentActivity($departmentId, $startDate, $endDate),
        ];
    }

    /**
     * Get main statistics
     */
    private function getMainStats(?int $departmentId, ?string $startDate, ?string $endDate): array
    {
        $currentEntries = $this->getEntriesCount($departmentId, false, $startDate, $endDate);
        $previousEntries = $this->getEntriesCount($departmentId, true, $startDate, $endDate);
        $percentChange = $this->calculatePercentChange($currentEntries, $previousEntries);

        $totalUsers = $departmentId
            ? User::whereHas('employee', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })->count()
            : User::count();

        $completionRate = $this->getCompletionRate($departmentId, $startDate, $endDate);
        $departments = Department::count();

        return [
            'total_entries' => $currentEntries,
            'entries_change' => $percentChange,
            'total_users' => $totalUsers,
            'completion_rate' => $completionRate,
            'total_departments' => $departments,
            'active_users_today' => $this->getActiveUsersToday($departmentId),
        ];
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(?int $departmentId, ?string $startDate, ?string $endDate): array
    {
        $averageResponseTime = $this->getAverageResponseTime($departmentId, $startDate, $endDate);
        $successRate = $this->getSuccessRate($departmentId, $startDate, $endDate);
        $pendingQueue = $this->getPendingQueue($departmentId);

        return [
            'avg_response_time' => $averageResponseTime,
            'target_processing_time' => 24, // Target 24 hours
            'success_rate' => $successRate,
            'success_rate_change' => $this->getSuccessRateChange($departmentId, $startDate, $endDate),
            'pending_queue' => $pendingQueue,
        ];
    }

    /**
     * Get trend data for charts
     */
    private function getTrendData(?int $departmentId, ?string $startDate, ?string $endDate): array
    {
        $days = $this->getDateRange($startDate, $endDate, 7); // Last 7 days
        $trendData = [];

        foreach ($days as $day) {
            $dayStart = $day->format('Y-m-d');
            $dayEnd = $day->format('Y-m-d');

            $count = $this->getEntriesCount($departmentId, false, $dayStart, $dayEnd);

            $trendData[] = [
                'date' => $day->format('M d'),
                'count' => $count,
            ];
        }

        return $trendData;
    }

    /**
     * Get department breakdown
     */
    private function getDepartmentBreakdown(?string $startDate, ?string $endDate): array
    {
        $departments = Department::with(['employees'])->get();
        $totalEntries = $this->getEntriesCount(null, false, $startDate, $endDate);

        return $departments->map(function ($department) use ($startDate, $endDate, $totalEntries) {
            $entriesCount = $this->getEntriesCount($department->id, false, $startDate, $endDate);
            $percentage = $totalEntries > 0 ? round(($entriesCount / $totalEntries) * 100, 1) : 0;

            return [
                'name' => $department->name,
                'count' => $entriesCount,
                'percentage' => $percentage,
            ];
        })->toArray();
    }

    /**
     * Get status distribution
     */
    private function getStatusDistribution(?int $departmentId, ?string $startDate, ?string $endDate): array
    {
        $query = KpiDailyEntry::query();

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('entry_date', [$startDate, $endDate]);
        } else {
            $query->whereMonth('entry_date', Carbon::now()->format('m'))
                  ->whereYear('entry_date', Carbon::now()->format('Y'));
        }

        $statusCounts = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $totalEntries = $statusCounts->sum('count');

        $statuses = ['approved', 'submitted', 'rejected'];
        $result = [];

        foreach ($statuses as $status) {
            $count = $statusCounts->get($status)->count ?? 0;
            $percentage = $totalEntries > 0 ? round(($count / $totalEntries) * 100, 1) : 0;

            $result[] = [
                'status' => $status,
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return $result;
    }

    /**
     * Get top performers
     */
    private function getTopPerformers(?int $departmentId, ?string $startDate, ?string $endDate, int $limit = 5): array
    {
        $query = User::select('users.id', 'users.name')
            ->join('employees', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('kpi_daily_entries', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'kpi_daily_entries.user_id');
                if ($startDate && $endDate) {
                    $join->whereBetween('kpi_daily_entries.entry_date', [$startDate, $endDate]);
                } else {
                    $join->whereMonth('kpi_daily_entries.entry_date', Carbon::now()->format('m'))
                         ->whereYear('kpi_daily_entries.entry_date', Carbon::now()->format('Y'));
                }
            })
            ->selectRaw('users.id, users.name, departments.name as department_name, COUNT(kpi_daily_entries.id) as entries_count')
            ->groupBy('users.id', 'users.name', 'departments.name');

        if ($departmentId) {
            $query->where('employees.department_id', $departmentId);
        }

        return $query->orderBy('entries_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'department' => $user->department_name,
                    'entries_count' => $user->entries_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(?int $departmentId, ?string $startDate, ?string $endDate, int $limit = 10): array
    {
        $query = KpiDailyEntry::select('kpi_daily_entries.*', 'users.name as user_name', 'departments.name as department_name')
            ->join('users', 'kpi_daily_entries.user_id', '=', 'users.id')
            ->join('employees', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->orderBy('kpi_daily_entries.updated_at', 'desc');

        if ($departmentId) {
            $query->where('employees.department_id', $departmentId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('kpi_daily_entries.entry_date', [$startDate, $endDate]);
        } else {
            $query->where('kpi_daily_entries.updated_at', '>=', Carbon::now()->subDays(7));
        }

        return $query->limit($limit)
            ->get()
            ->map(function ($entry) {
                return [
                    'user_name' => $entry->user_name,
                    'department' => $entry->department_name,
                    'status' => $entry->status,
                    'action' => $this->getActionText($entry->status),
                    'time_ago' => Carbon::parse($entry->updated_at)->diffForHumans(),
                ];
            })
            ->toArray();
    }

    // Helper methods

    private function getActiveUsersToday(?int $departmentId): int
    {
        $query = User::whereExists(function ($subQuery) {
            $subQuery->select(DB::raw(1))
                ->from('kpi_daily_entries')
                ->whereColumn('kpi_daily_entries.user_id', 'users.id')
                ->whereDate('kpi_daily_entries.entry_date', Carbon::today());
        });

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        return $query->count();
    }

    private function getAverageResponseTime(?int $departmentId, ?string $startDate, ?string $endDate): float
    {
        $query = KpiDailyEntry::whereNotNull('created_at')
            ->whereNotNull('updated_at');

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('entry_date', [$startDate, $endDate]);
        } else {
            $query->whereMonth('entry_date', Carbon::now()->format('m'))
                  ->whereYear('entry_date', Carbon::now()->format('Y'));
        }

        $entries = $query->get();

        if ($entries->isEmpty()) return 0;

        $totalMinutes = $entries->sum(function ($entry) {
            return Carbon::parse($entry->created_at)->diffInMinutes(Carbon::parse($entry->updated_at));
        });

        return round($totalMinutes / $entries->count(), 2);
    }

    private function getSuccessRate(?int $departmentId, ?string $startDate, ?string $endDate): int
    {
        $totalEntries = $this->getEntriesCount($departmentId, false, $startDate, $endDate);

        if ($totalEntries === 0) return 0;

        $query = KpiDailyEntry::where('status', 'approved');

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('entry_date', [$startDate, $endDate]);
        } else {
            $query->whereMonth('entry_date', Carbon::now()->format('m'))
                  ->whereYear('entry_date', Carbon::now()->format('Y'));
        }

        $completedEntries = $query->count();

        return round(($completedEntries / $totalEntries) * 100);
    }

    private function getSuccessRateChange(?int $departmentId, ?string $startDate, ?string $endDate): float
    {
        $currentRate = $this->getSuccessRate($departmentId, $startDate, $endDate);

        // Get previous period success rate
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $daysDiff = $start->diffInDays($end);

            $prevStart = $start->copy()->subDays($daysDiff)->format('Y-m-d');
            $prevEnd = $start->copy()->subDay()->format('Y-m-d');
        } else {
            $prevStart = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
            $prevEnd = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        }

        $previousRate = $this->getSuccessRate($departmentId, $prevStart, $prevEnd);

        return $this->calculatePercentChange($currentRate, $previousRate);
    }

    private function getPendingQueue(?int $departmentId): int
    {
        $query = KpiDailyEntry::where('status', 'submitted');

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        return $query->count();
    }

    private function getDateRange(?string $startDate, ?string $endDate, int $defaultDays = 30): array
    {
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
        } else {
            $end = Carbon::now();
            $start = $end->copy()->subDays($defaultDays - 1);
        }

        $days = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $days[] = $current->copy();
            $current->addDay();
        }

        return $days;
    }

    private function getActionText(string $status): string
    {
        return match($status) {
            'completed' => 'Completed KPI entry',
            'pending' => 'Submitted KPI entry for review',
            'processing' => 'KPI entry is being processed',
            'rejected' => 'KPI entry was rejected',
            default => 'Updated KPI entry'
        };
    }

    // Public methods

    public function getEntriesCount(
        ?int $departmentId = null,
        bool $previousMonth = false,
        ?string $startDate = null,
        ?string $endDate = null
    ): int {
        $query = KpiDailyEntry::query();

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($startDate && $endDate) {
            if ($previousMonth) {
                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);
                $daysDiff = $start->diffInDays($end);

                $prevStart = $start->copy()->subDays($daysDiff + 1);
                $prevEnd = $start->copy()->subDay();

                $query->whereBetween('entry_date', [$prevStart->format('Y-m-d'), $prevEnd->format('Y-m-d')]);
            } else {
                $query->whereBetween('entry_date', [$startDate, $endDate]);
            }
        } else {
            if ($previousMonth) {
                $query->whereMonth('entry_date', Carbon::now()->subMonth()->format('m'))
                      ->whereYear('entry_date', Carbon::now()->subMonth()->format('Y'));
            } else {
                $query->whereMonth('entry_date', Carbon::now()->format('m'))
                      ->whereYear('entry_date', Carbon::now()->format('Y'));
            }
        }

        return $query->count();
    }

    public function calculatePercentChange(int $current, int $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function getCompletionRate(?int $departmentId = null, ?string $startDate = null, ?string $endDate = null): int
    {
        $userCount = $departmentId
            ? User::whereHas('employee', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })->count()
            : User::whereHas('employee')->count();

        if ($userCount <= 0) {
            return 0;
        }

        $workdaysInPeriod = $this->getWorkdaysInRange($startDate, $endDate);
        $expectedEntries = $userCount * $workdaysInPeriod;

        if ($expectedEntries <= 0) {
            return 0;
        }

        $actualEntries = $this->getEntriesCount($departmentId, false, $startDate, $endDate);

        return round(($actualEntries / $expectedEntries) * 100);
    }

    private function getWorkdaysInRange(?string $startDate, ?string $endDate): int
    {
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $workdays = 0;

            $current = $start->copy();
            while ($current->lte($end)) {
                if ($current->isWeekday()) {
                    $workdays++;
                }
                $current->addDay();
            }

            return $workdays;
        }

        return $this->getWorkdaysInMonth();
    }

    public function getWorkdaysInMonth(Carbon $date = null): int
    {
        $now = $date ?? Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // If current month, only count up to today
        if ($now->month === Carbon::now()->month && $now->year === Carbon::now()->year) {
            $endOfMonth = Carbon::now();
        }

        $workdays = 0;
        $current = $startOfMonth->copy();

        while ($current->lte($endOfMonth)) {
            if ($current->isWeekday()) {
                $workdays++;
            }
            $current->addDay();
        }

        return $workdays;
    }
}