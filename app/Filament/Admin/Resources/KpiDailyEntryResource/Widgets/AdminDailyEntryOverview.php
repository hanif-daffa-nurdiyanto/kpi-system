<?php

namespace App\Filament\Admin\Resources\KpiDailyEntryResource\Widgets;

use App\Models\KpiDailyEntry;
use App\Models\KpiMetric;
use App\Models\User;
use App\Models\Department;
use App\Services\KpiStatsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdminDailyEntryOverview extends BaseWidget
{
    protected int $cacheMinutes = 10; // Longer cache for admin due to more data
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        try {
            return [
                $this->getTodayEntriesStat(),
                $this->getWeeklyEntriesStat(),
                $this->getMonthlyEntriesStat(),
                $this->getPendingEntriesStat(),
                $this->getTopDepartmentStat(),
                $this->getSystemHealthStat(),
            ];
        } catch (\Exception $e) {
            Log::error('Error in AdminDailyEntryOverview::getStats: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                Stat::make('System Error', 'Unable to load data')
                    ->description('Please check system logs')
                    ->color('danger')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
            ];
        }
    }

    /**
     * Get today's entries with detailed breakdown
     */
    protected function getTodayEntriesStat(): Stat
    {
        $cacheKey = $this->getCacheKey('today_entries');

        $data = Cache::remember($cacheKey, $this->cacheMinutes, function () {
            return $this->fetchTodayData();
        });

        $description = $this->formatTodayDescription($data);
        $color = $this->getColorByStatus($data['approved'], $data['total']);

        return Stat::make('Today\'s Entries', $data['total'])
            ->description($description)
            ->descriptionIcon('heroicon-m-calendar-days')
            ->color($color)
            ->chart($this->getTodayChart());
    }

    /**
     * Get weekly entries with department breakdown
     */
    protected function getWeeklyEntriesStat(): Stat
    {
        $cacheKey = $this->getCacheKey('weekly_entries');

        $data = Cache::remember($cacheKey, $this->cacheMinutes, function () {
            return $this->fetchWeeklyData();
        });

        $description = "Approved: {$data['approved']} | Pending: {$data['submitted']} | Total: {$data['total']}";

        return Stat::make('This Week', $data['approved'])
            ->description($description)
            ->descriptionIcon('heroicon-m-chart-bar')
            ->color('success')
            ->chart($this->getWeeklyChart());
    }

    /**
     * Get monthly entries with trend analysis
     */
    protected function getMonthlyEntriesStat(): Stat
    {
        $cacheKey = $this->getCacheKey('monthly_entries');

        $data = Cache::remember($cacheKey, $this->cacheMinutes, function () {
            return $this->fetchMonthlyData();
        });

        $trend = $this->calculateTrend($data['current'], $data['previous']);
        $description = "vs Last Month: {$trend}% | Total: {$data['total']}";

        return Stat::make('This Month', $data['current'])
            ->description($description)
            ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($trend >= 0 ? 'success' : 'warning')
            ->chart($this->getMonthlyChart());
    }

    /**
     * Get pending entries requiring attention
     */
    protected function getPendingEntriesStat(): Stat
    {
        $cacheKey = $this->getCacheKey('pending_entries');

        $data = Cache::remember($cacheKey, $this->cacheMinutes, function () {
            return $this->fetchPendingData();
        });

        $description = $this->formatPendingDescription($data);
        $color = $data['total'] > 50 ? 'danger' : ($data['total'] > 20 ? 'warning' : 'info');

        return Stat::make('Pending Reviews', $data['total'])
            ->description($description)
            ->descriptionIcon('heroicon-m-clock')
            ->color($color);
    }

    /**
     * Get top performing department
     */
    protected function getTopDepartmentStat(): Stat
    {
        $cacheKey = $this->getCacheKey('top_department');

        $data = Cache::remember($cacheKey, $this->cacheMinutes, function () {
            return $this->fetchTopDepartmentData();
        });

        $description = $data['name'] ?
            "This month: {$data['entries']} entries" :
            'No department data available';

        return Stat::make('Top Department', $data['name'] ?: 'N/A')
            ->description($description)
            ->descriptionIcon('heroicon-m-building-office-2')
            ->color('primary');
    }

    /**
     * Get system health overview
     */
    protected function getSystemHealthStat(): Stat
    {
        $cacheKey = $this->getCacheKey('system_health');

        $data = Cache::remember($cacheKey, $this->cacheMinutes, function () {
            return $this->fetchSystemHealthData();
        });

        $description = $this->formatSystemHealthDescription($data);
        $color = $this->getSystemHealthColor($data['completion_rate']);

        return Stat::make('Completion Rate', $data['completion_rate'] . '%')
            ->description($description)
            ->descriptionIcon('heroicon-m-chart-pie')
            ->color($color);
    }

    /**
     * Fetch today's data with status breakdown
     */
    protected function fetchTodayData(): array
    {
        $today = Carbon::today()->format('Y-m-d');

        $totals = KpiDailyEntry::whereDate('entry_date', $today)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = "submitted" THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected
            ')
            ->first();

        return [
            'total' => $totals->total ?? 0,
            'approved' => $totals->approved ?? 0,
            'submitted' => $totals->submitted ?? 0,
            'rejected' => $totals->rejected ?? 0,
        ];
    }

    /**
     * Fetch weekly data
     */
    protected function fetchWeeklyData(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');

        $totals = KpiDailyEntry::whereBetween('entry_date', [$startOfWeek, $endOfWeek])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = "submitted" THEN 1 ELSE 0 END) as submitted
            ')
            ->first();

        return [
            'total' => $totals->total ?? 0,
            'approved' => $totals->approved ?? 0,
            'submitted' => $totals->submitted ?? 0,
        ];
    }

    /**
     * Fetch monthly data with trend
     */
    protected function fetchMonthlyData(): array
    {
        $currentMonth = Carbon::now();
        $previousMonth = Carbon::now()->subMonth();

        $current = KpiDailyEntry::whereMonth('entry_date', $currentMonth->month)
            ->whereYear('entry_date', $currentMonth->year)
            ->where('status', 'approved')
            ->count();

        $previous = KpiDailyEntry::whereMonth('entry_date', $previousMonth->month)
            ->whereYear('entry_date', $previousMonth->year)
            ->where('status', 'approved')
            ->count();

        $total = KpiDailyEntry::whereMonth('entry_date', $currentMonth->month)
            ->whereYear('entry_date', $currentMonth->year)
            ->count();

        return [
            'current' => $current,
            'previous' => $previous,
            'total' => $total,
        ];
    }

    /**
     * Fetch pending data with urgency breakdown
     */
    protected function fetchPendingData(): array
    {
        $total = KpiDailyEntry::where('status', 'submitted')->count();

        $urgent = KpiDailyEntry::where('status', 'submitted')
            ->where('entry_date', '<=', Carbon::now()->subDays(3))
            ->count();

        $today = KpiDailyEntry::where('status', 'submitted')
            ->whereDate('entry_date', Carbon::today())
            ->count();

        return [
            'total' => $total,
            'urgent' => $urgent,
            'today' => $today,
        ];
    }

    /**
     * Fetch top department data
     */
    protected function fetchTopDepartmentData(): array
    {
        $currentMonth = Carbon::now();

        $topDepartment = DB::table('kpi_daily_entries as kde')
            ->join('users as u', 'kde.user_id', '=', 'u.id')
            ->join('employees as e', 'u.id', '=', 'e.user_id')
            ->join('departments as d', 'e.department_id', '=', 'd.id')
            ->whereMonth('kde.entry_date', $currentMonth->month)
            ->whereYear('kde.entry_date', $currentMonth->year)
            ->where('kde.status', 'approved')
            ->groupBy('d.id', 'd.name')
            ->selectRaw('d.name, COUNT(*) as entries')
            ->orderBy('entries', 'desc')
            ->first();

        return [
            'name' => $topDepartment->name ?? null,
            'entries' => $topDepartment->entries ?? 0,
        ];
    }

    /**
     * Fetch system health data
     */
    protected function fetchSystemHealthData(): array
    {
        $statsService = app(KpiStatsService::class);

        $completionRate = $statsService->getCompletionRate(
            null,
            null,
            null
        );

        $totalUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'employee');
        })->count();

        $activeUsers = KpiDailyEntry::whereMonth('entry_date', Carbon::now()->month)
            ->whereYear('entry_date', Carbon::now()->year)
            ->distinct('user_id')
            ->count();

        $avgEntriesPerUser = $activeUsers > 0
            ? round(KpiDailyEntry::whereMonth('entry_date', Carbon::now()->month)
                ->whereYear('entry_date', Carbon::now()->year)
                ->where('status', 'approved')
                ->count() / $activeUsers, 1)
            : 0;

        return [
            'completion_rate' => $completionRate,
            'active_users'    => $activeUsers,
            'total_users'     => $totalUsers,
            'avg_entries'     => $avgEntriesPerUser,
        ];
    }

    /**
     * Generate today's hourly chart
     */
    protected function getTodayChart(): array
    {
        $cacheKey = $this->getCacheKey('today_chart');

        return Cache::remember($cacheKey, $this->cacheMinutes, function () {
            $data = collect(range(0, 23))->map(function ($hour) {
                return KpiDailyEntry::whereDate('entry_date', Carbon::today())
                    ->whereTime('created_at', '>=', sprintf('%02d:00:00', $hour))
                    ->whereTime('created_at', '<', sprintf('%02d:59:59', $hour))
                    ->count();
            });

            return $data->toArray();
        });
    }

    /**
     * Generate weekly chart (last 12 weeks)
     */
    protected function getWeeklyChart(): array
    {
        $cacheKey = $this->getCacheKey('weekly_chart');

        return Cache::remember($cacheKey, $this->cacheMinutes, function () {
            $data = collect(range(0, 11))->map(function ($weeksAgo) {
                $startOfWeek = Carbon::now()->subWeeks($weeksAgo)->startOfWeek();
                $endOfWeek = Carbon::now()->subWeeks($weeksAgo)->endOfWeek();

                return KpiDailyEntry::whereBetween('entry_date', [
                    $startOfWeek->format('Y-m-d'),
                    $endOfWeek->format('Y-m-d')
                ])
                ->where('status', 'approved')
                ->count();
            });

            return $data->reverse()->values()->toArray();
        });
    }

    /**
     * Generate monthly chart (last 12 months)
     */
    protected function getMonthlyChart(): array
    {
        $cacheKey = $this->getCacheKey('monthly_chart');

        return Cache::remember($cacheKey, $this->cacheMinutes, function () {
            $data = collect(range(0, 11))->map(function ($monthsAgo) {
                $date = Carbon::now()->subMonths($monthsAgo);

                return KpiDailyEntry::whereMonth('entry_date', $date->month)
                    ->whereYear('entry_date', $date->year)
                    ->where('status', 'approved')
                    ->count();
            });

            return $data->reverse()->values()->toArray();
        });
    }

    /**
     * Format today's description
     */
    protected function formatTodayDescription(array $data): string
    {
        if ($data['total'] === 0) {
            return 'No entries submitted today';
        }

        $parts = [];
        if ($data['approved'] > 0) $parts[] = "Approved: {$data['approved']}";
        if ($data['submitted'] > 0) $parts[] = "Pending: {$data['submitted']}";
        if ($data['rejected'] > 0) $parts[] = "Rejected: {$data['rejected']}";

        return implode(' | ', $parts);
    }

    /**
     * Format pending description
     */
    protected function formatPendingDescription(array $data): string
    {
        if ($data['total'] === 0) {
            return 'All entries reviewed';
        }

        $parts = [];
        if ($data['urgent'] > 0) $parts[] = "Urgent: {$data['urgent']}";
        if ($data['today'] > 0) $parts[] = "Today: {$data['today']}";

        return !empty($parts) ? implode(' | ', $parts) : 'Requires review';
    }

    /**
     * Format system health description
     */
    protected function formatSystemHealthDescription(array $data): string
    {
        return "Active: {$data['active_users']}/{$data['total_users']} users | Avg: {$data['avg_entries']} entries/user";
    }

    /**
     * Get color based on approval status
     */
    protected function getColorByStatus(int $approved, int $total): string
    {
        if ($total === 0) return 'gray';

        $rate = ($approved / $total) * 100;

        if ($rate >= 80) return 'success';
        if ($rate >= 60) return 'warning';
        return 'danger';
    }

    /**
     * Get system health color
     */
    protected function getSystemHealthColor(int $rate): string
    {
        if ($rate >= 80) return 'success';
        if ($rate >= 60) return 'warning';
        return 'danger';
    }

    /**
     * Calculate trend percentage
     */
    protected function calculateTrend(int $current, int $previous): int
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100);
    }

    /**
     * Generate cache key for admin context
     */
    protected function getCacheKey(string $type): string
    {
        $date = Carbon::now()->format('Y-m-d-H');
        return "admin_kpi_widget_{$type}_{$date}";
    }

    /**
     * Clear all admin cache
     */
    public function clearCache(): void
    {
        $types = [
            'today_entries', 'weekly_entries', 'monthly_entries',
            'pending_entries', 'top_department', 'system_health',
            'today_chart', 'weekly_chart', 'monthly_chart'
        ];

        foreach ($types as $type) {
            Cache::forget($this->getCacheKey($type));
        }
    }

    /**
     * Get widgets columns count
     */
    protected function getColumns(): int
    {
        return 3; // Display in 3 columns for better layout
    }
}