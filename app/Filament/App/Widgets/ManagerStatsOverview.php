<?php

namespace App\Filament\App\Widgets;

use App\Models\Department;
use App\Models\Employee;
use App\Models\KpiDailyEntry;
use App\Models\KpiEntryDetail;
use App\Models\TeamGoals;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManagerStatsOverview extends Widget
{
    protected static string $view = 'filament.app.widgets.manager-stats-cards';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 1;

    public $cards = [];
    public $department = null;
    public $employeeCount = 0;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('manager');
    }

    public function mount(): void
    {
        $this->loadManagerData();
        $this->cards = $this->generateStats();
    }

    protected function loadManagerData(): void
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('manager')) {
            return;
        }

        $this->department = Department::where('manager_id', $user->id)->first();

        if ($this->department) {
            $this->employeeCount = Employee::where('department_id', $this->department->id)->count();
        }
    }

    protected function generateStats(): array
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('manager') || !$this->department) {
                return $this->getEmptyState();
            }

            $employees = Employee::where('department_id', $this->department->id)
                ->with('user')
                ->get();

            $employeeIds = $employees->pluck('user_id')->toArray();

            if (empty($employeeIds)) {
                return $this->getEmptyState();
            }

            $today = Carbon::today();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $last7Days = Carbon::now()->subDays(7);

            $totalEmployees = count($employeeIds);

            $submissionsToday = KpiDailyEntry::whereIn('user_id', $employeeIds)
                ->whereDate('entry_date', $today)
                ->count();

            $approvedToday = KpiDailyEntry::whereIn('user_id', $employeeIds)
                ->whereDate('entry_date', $today)
                ->where('status', 'approved')
                ->count();

            $pendingApprovals = KpiDailyEntry::whereIn('user_id', $employeeIds)
                ->where('status', 'submitted')
                ->count();

            $todayStats = $this->calculateDailyPerformance($employeeIds, $today);

            $monthlyStats = $this->calculatePeriodPerformance($employeeIds, $startOfMonth, $endOfMonth);

            $activeGoals = TeamGoals::where('department_id', $this->department->id)
                ->where('is_active', true)
                ->count();

            $weeklyProductivity = KpiDailyEntry::whereIn('user_id', $employeeIds)
                ->where('entry_date', '>=', $last7Days)
                ->where('status', 'approved')
                ->count();

            $submissionRate = $totalEmployees > 0 ? round(($submissionsToday / $totalEmployees) * 100, 1) : 0;

            return [
                [
                    'title' => 'Total Team Members',
                    'value' => $totalEmployees,
                    'icon' => 'heroicon-o-user-group',
                    'color' => 'text-blue-600',
                    'bg' => 'bg-blue-50',
                    'description' => 'Active employees in ' . ($this->department->name ?? 'your department'),
                    'trend' => null
                ],
                [
                    'title' => 'Today\'s Submissions',
                    'value' => $submissionsToday,
                    'subtitle' => "({$approvedToday} approved)",
                    'icon' => 'heroicon-o-document-text',
                    'color' => $submissionRate >= 80 ? 'text-green-600' : ($submissionRate >= 60 ? 'text-yellow-600' : 'text-red-600'),
                    'bg' => $submissionRate >= 80 ? 'bg-green-50' : ($submissionRate >= 60 ? 'bg-yellow-50' : 'bg-red-50'),
                    'description' => "Submission rate: {$submissionRate}%",
                    'trend' => $this->getTrendIcon($submissionRate, 80)
                ],
                [
                    'title' => 'Pending Approvals',
                    'value' => $pendingApprovals,
                    'icon' => 'heroicon-o-clock',
                    'color' => $pendingApprovals > 5 ? 'text-red-600' : ($pendingApprovals > 0 ? 'text-yellow-600' : 'text-green-600'),
                    'bg' => $pendingApprovals > 5 ? 'bg-red-50' : ($pendingApprovals > 0 ? 'bg-yellow-50' : 'bg-green-50'),
                    'description' => $pendingApprovals > 0 ? 'Entries waiting for your approval' : 'All caught up!',
                    'priority' => $pendingApprovals > 5 ? 'high' : ($pendingApprovals > 0 ? 'medium' : 'low')
                ],
                [
                    'title' => 'Today\'s Avg Performance',
                    'value' => $todayStats['avg_progress'] . '%',
                    'icon' => 'heroicon-o-chart-bar-square',
                    'color' => $this->getPerformanceColor($todayStats['avg_progress']),
                    'bg' => $this->getPerformanceBg($todayStats['avg_progress']),
                    'description' => "Based on {$todayStats['valid_entries']} approved entries",
                    'trend' => $this->getTrendIcon($todayStats['avg_progress'], 80)
                ],
                [
                    'title' => 'Monthly Avg Performance',
                    'value' => $monthlyStats['avg_progress'] . '%',
                    'icon' => 'heroicon-o-chart-pie',
                    'color' => $this->getPerformanceColor($monthlyStats['avg_progress']),
                    'bg' => $this->getPerformanceBg($monthlyStats['avg_progress']),
                    'description' => "From {$monthlyStats['valid_entries']} total entries this month",
                    'trend' => $this->getTrendIcon($monthlyStats['avg_progress'], 75)
                ],
                [
                    'title' => 'Targets Achieved Today',
                    'value' => $todayStats['achieved_targets'],
                    'subtitle' => "of {$todayStats['valid_entries']} entries",
                    'icon' => 'heroicon-o-trophy',
                    'color' => 'text-green-600',
                    'bg' => 'bg-green-50',
                    'description' => 'KPI targets met (≥100% achievement)',
                    'achievement_rate' => $todayStats['valid_entries'] > 0 ? round(($todayStats['achieved_targets'] / $todayStats['valid_entries']) * 100, 1) : 0
                ],
                [
                    'title' => 'Below Target Today',
                    'value' => $todayStats['below_targets'],
                    'subtitle' => "(<70% achievement)",
                    'icon' => 'heroicon-o-exclamation-triangle',
                    'color' => $todayStats['below_targets'] > 0 ? 'text-red-600' : 'text-gray-600',
                    'bg' => $todayStats['below_targets'] > 0 ? 'bg-red-50' : 'bg-gray-50',
                    'description' => $todayStats['below_targets'] > 0 ? 'Need attention and support' : 'Great performance overall!',
                    'priority' => $todayStats['below_targets'] > 2 ? 'high' : ($todayStats['below_targets'] > 0 ? 'medium' : 'low')
                ],
                [
                    'title' => 'Weekly Productivity',
                    'value' => $weeklyProductivity,
                    'subtitle' => 'approved entries',
                    'icon' => 'heroicon-o-calendar-days',
                    'color' => 'text-purple-600',
                    'bg' => 'bg-purple-50',
                    'description' => 'Total approved entries in last 7 days',
                    'daily_avg' => round($weeklyProductivity / 7, 1)
                ],
                [
                    'title' => 'Active Team Goals',
                    'value' => $activeGoals,
                    'icon' => 'heroicon-o-flag',
                    'color' => 'text-indigo-600',
                    'bg' => 'bg-indigo-50',
                    'description' => $activeGoals > 0 ? 'Current department objectives' : 'No active goals set',
                    'status' => $activeGoals > 0 ? 'active' : 'none'
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Manager Stats Overview Error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'department_id' => $this->department?->id,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->getErrorState();
        }
    }

    protected function calculateDailyPerformance(array $employeeIds, Carbon $date): array
    {
        $entries = KpiEntryDetail::whereHas('kpiDailyEntry', function($q) use ($employeeIds, $date) {
            $q->whereIn('user_id', $employeeIds)
              ->whereDate('entry_date', $date)
              ->where('status', 'approved');
        })->with('kpiMetric')->get();

        return $this->processPerformanceEntries($entries);
    }

    protected function calculatePeriodPerformance(array $employeeIds, Carbon $start, Carbon $end): array
    {
        $entries = KpiEntryDetail::whereHas('kpiDailyEntry', function($q) use ($employeeIds, $start, $end) {
            $q->whereIn('user_id', $employeeIds)
              ->whereBetween('entry_date', [$start, $end])
              ->where('status', 'approved');
        })->with('kpiMetric')->get();

        return $this->processPerformanceEntries($entries);
    }

    protected function processPerformanceEntries($entries): array
    {
        $totalProgress = 0;
        $achievedTargets = 0;
        $belowTargets = 0;
        $validEntries = 0;

        foreach ($entries as $entry) {
            if ($entry->kpiMetric && $entry->kpiMetric->target_value > 0) {
                $progress = ($entry->value / $entry->kpiMetric->target_value) * 100;
                $totalProgress += $progress;
                $validEntries++;

                if ($progress >= 100) {
                    $achievedTargets++;
                } elseif ($progress < 70) {
                    $belowTargets++;
                }
            }
        }

        return [
            'avg_progress' => $validEntries > 0 ? round($totalProgress / $validEntries, 1) : 0,
            'achieved_targets' => $achievedTargets,
            'below_targets' => $belowTargets,
            'valid_entries' => $validEntries,
            'total_progress' => $totalProgress
        ];
    }

    protected function getPerformanceColor(float $percentage): string
    {
        return $percentage >= 90 ? 'text-green-600' :
               ($percentage >= 70 ? 'text-yellow-600' : 'text-red-600');
    }

    protected function getPerformanceBg(float $percentage): string
    {
        return $percentage >= 90 ? 'bg-green-50' :
               ($percentage >= 70 ? 'bg-yellow-50' : 'bg-red-50');
    }

    protected function getTrendIcon(float $value, float $threshold): string
    {
        if ($value >= $threshold) {
            return 'trending-up';
        } elseif ($value >= $threshold * 0.8) {
            return 'trending-stable';
        }
        return 'trending-down';
    }

    protected function getEmptyState(): array
    {
        return [
            [
                'title' => 'No Department Assigned',
                'value' => 0,
                'icon' => 'heroicon-o-building-office-2',
                'color' => 'text-gray-600',
                'bg' => 'bg-gray-50',
                'description' => 'You are not assigned to manage any department yet.'
            ]
        ];
    }

    protected function getErrorState(): array
    {
        return [
            [
                'title' => 'Data Error',
                'value' => '!',
                'icon' => 'heroicon-o-exclamation-triangle',
                'color' => 'text-red-600',
                'bg' => 'bg-red-50',
                'description' => 'Unable to load dashboard data. Please refresh the page.'
            ]
        ];
    }

    public function getDepartmentName(): string
    {
        return $this->department?->name ?? 'Unknown Department';
    }

    public function getEmployeeCount(): int
    {
        return $this->employeeCount;
    }

    public function getLastUpdated(): string
    {
        return Carbon::now()->format('M d, Y H:i');
    }
}