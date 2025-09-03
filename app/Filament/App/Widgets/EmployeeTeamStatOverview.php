<?php

namespace App\Filament\App\Widgets;

use App\Models\Department;
use App\Models\Employee;
use App\Models\KpiDailyEntry;
use App\Models\KpiEntryDetail;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeTeamStatOverview extends BaseWidget
{
    protected static string $view = 'filament.app.widgets.employee-team-state-overview';
    protected int|string|array $columnSpan = [
        'default' => 6,
        'sm' => 6,
        'md' => 6,
        'lg' => 6,
        'xl' => 6,
        '2xl' => 6,
    ];

    public array $teamStats = [];
    public array $individualStats = [];
    public string $departmentName = '';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole('employee');
    }

    public function mount(): void
    {
        $this->generateStats();
    }

    public function getColumnSpan(): int | string | array
    {
        return [
            'default' => 6,
            'sm' => 6,
            'md' => 6,
            'lg' => 6,
            'xl' => 6,
            '2xl' => 6,
        ];
    }

    protected function generateStats(): void
    {
        $user = Auth::user();

        $department = Department::where('manager_id', $user->id)->first();

        if (!$department) {
            $employee = Employee::where('user_id', $user->id)->with('department')->first();
            $department = $employee?->department;
        }

        if (!$department) {
            $this->teamStats = [];
            $this->individualStats = [];
            return;
        }

        $this->departmentName = $department->name;
        $employees = Employee::where('department_id', $department->id)
            ->with('user')
            ->get();

        $employeeIds = $employees->pluck('user_id')->toArray();

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $this->teamStats = $this->generateTeamStats($employeeIds, $monthStart, $monthEnd, $today, $employees->count());
        $this->individualStats = $this->generateIndividualStats($employees, $monthStart, $monthEnd, $today);
    }

    protected function generateTeamStats(array $employeeIds, Carbon $monthStart, Carbon $monthEnd, Carbon $today, int $totalEmployees): array
    {

        $submissionsToday = KpiDailyEntry::whereIn('user_id', $employeeIds)
            ->whereDate('entry_date', $today)
            ->whereIn('status', ['submitted', 'approved', 'rejected'])
            ->count();

        $pendingApprovals = KpiDailyEntry::whereIn('user_id', $employeeIds)
            ->whereDate('entry_date', $today)
            ->where('status', 'submitted')
            ->count();

        $totalSubmissionsMTD = KpiDailyEntry::whereIn('user_id', $employeeIds)
            ->whereBetween('entry_date', [$monthStart, $monthEnd])
            ->whereIn('status', ['submitted', 'approved', 'rejected'])
            ->count();

        $approvedSubmissionsMTD = KpiDailyEntry::whereIn('user_id', $employeeIds)
            ->whereBetween('entry_date', [$monthStart, $monthEnd])
            ->where('status', 'approved')
            ->count();

        $entries = KpiEntryDetail::whereHas('kpiDailyEntry', function ($q) use ($employeeIds, $monthStart, $monthEnd) {
            $q->whereIn('user_id', $employeeIds)
            ->whereBetween('entry_date', [$monthStart, $monthEnd])
            ->where('status', 'approved');
        })->with('kpiMetric')->get();

        $totalProgress = 0;
        $validEntries = 0;

        foreach ($entries as $entry) {
            if ($entry->kpiMetric && $entry->kpiMetric->target_value > 0) {
                $progress = ($entry->value / $entry->kpiMetric->target_value) * 100;
                $totalProgress += $progress;
                $validEntries++;
            }
        }

        $avgPerformance = $validEntries > 0 ? round($totalProgress / $validEntries, 1) : 0;
        $approvalRate = $totalSubmissionsMTD > 0 ? round(($approvedSubmissionsMTD / $totalSubmissionsMTD) * 100, 1) : 0;

        $activeMembers = KpiDailyEntry::whereIn('user_id', $employeeIds)
            ->whereBetween('entry_date', [$monthStart, $monthEnd])
            ->whereIn('status', ['submitted', 'approved', 'rejected'])
            ->distinct('user_id')
            ->count();

        return [
            [
                'name' => 'Today\'s Activity',
                'value' => $submissionsToday,
                'target' => $totalEmployees,
                'unit' => 'submissions',
                'status' => $this->getActivityStatus($submissionsToday, $totalEmployees),
                'color' => $this->getActivityColor($submissionsToday, $totalEmployees),
                'icon' => 'heroicon-o-clock',
                'subtitle' => "Pending: $pendingApprovals",
            ],
            [
                'name' => 'Active Members',
                'value' => $activeMembers,
                'target' => $totalEmployees,
                'unit' => 'members',
                'status' => $this->getEngagementStatus($activeMembers, $totalEmployees),
                'color' => $this->getEngagementColor($activeMembers, $totalEmployees),
                'icon' => 'heroicon-o-user-group',
                'subtitle' => "Total: $totalEmployees",
            ],
            [
                'name' => 'Approval Rate (MTD)',
                'value' => $approvalRate,
                'target' => 100,
                'unit' => '%',
                'status' => $this->getApprovalStatus($approvalRate),
                'progress' => $approvalRate,
                'color' => $this->getApprovalColor($approvalRate),
                'icon' => 'heroicon-o-document-check',
                'subtitle' => "$approvedSubmissionsMTD/$totalSubmissionsMTD approved",
            ],
            [
                'name' => 'Team Performance',
                'value' => $avgPerformance,
                'target' => 100,
                'unit' => '%',
                'status' => $this->getPerformanceStatus($avgPerformance),
                'progress' => $avgPerformance,
                'color' => $this->getPerformanceColor($avgPerformance),
                'icon' => 'heroicon-o-chart-bar',
                'subtitle' => "Based on $validEntries entries",
            ],
        ];
    }

    private function getActivityStatus(int $submissions, int $totalEmployees): string
    {
        if ($totalEmployees == 0) return 'No Data';

        $rate = ($submissions / $totalEmployees) * 100;
        if ($rate >= 90) return 'Excellent';
        if ($rate >= 75) return 'Very Good';
        if ($rate >= 50) return 'Good';
        if ($rate >= 25) return 'Needs Attention';
        return 'Critical';
    }

    private function getActivityColor(int $submissions, int $totalEmployees): string
    {
        if ($totalEmployees == 0) return 'text-gray-600';

        $rate = ($submissions / $totalEmployees) * 100;
        if ($rate >= 90) return 'text-emerald-600';
        if ($rate >= 75) return 'text-green-600';
        if ($rate >= 50) return 'text-blue-600';
        if ($rate >= 25) return 'text-orange-600';
        return 'text-red-600';
    }

    private function getEngagementStatus(int $active, int $total): string
    {
        if ($total == 0) return 'No Data';

        $rate = ($active / $total) * 100;
        if ($rate >= 95) return 'Outstanding';
        if ($rate >= 85) return 'Excellent';
        if ($rate >= 70) return 'Good';
        if ($rate >= 50) return 'Fair';
        return 'Needs Improvement';
    }

    private function getEngagementColor(int $active, int $total): string
    {
        if ($total == 0) return 'text-gray-600';

        $rate = ($active / $total) * 100;
        if ($rate >= 95) return 'text-emerald-600';
        if ($rate >= 85) return 'text-green-600';
        if ($rate >= 70) return 'text-blue-600';
        if ($rate >= 50) return 'text-amber-600';
        return 'text-red-600';
    }

    private function getApprovalStatus(float $rate): string
    {
        if ($rate >= 95) return 'Excellent';
        if ($rate >= 85) return 'Very Good';
        if ($rate >= 75) return 'Good';
        if ($rate >= 60) return 'Fair';
        return 'Needs Improvement';
    }

    private function getApprovalColor(float $rate): string
    {
        if ($rate >= 95) return 'text-emerald-600';
        if ($rate >= 85) return 'text-green-600';
        if ($rate >= 75) return 'text-blue-600';
        if ($rate >= 60) return 'text-amber-600';
        return 'text-red-600';
    }

    private function getPerformanceStatus(float $performance): string
    {
        if ($performance >= 110) return 'Outstanding';
        if ($performance >= 100) return 'Target Achieved';
        if ($performance >= 85) return 'Near Target';
        if ($performance >= 70) return 'Making Progress';
        if ($performance >= 50) return 'Needs Attention';
        return 'Critical';
    }

    private function getPerformanceColor(float $performance): string
    {
        if ($performance >= 110) return 'text-emerald-600';
        if ($performance >= 100) return 'text-green-600';
        if ($performance >= 85) return 'text-blue-600';
        if ($performance >= 70) return 'text-amber-600';
        if ($performance >= 50) return 'text-orange-600';
        return 'text-red-600';
    }

    protected function generateIndividualStats($employees, Carbon $monthStart, Carbon $monthEnd, Carbon $today): array
    {
        $individualStats = [];

        foreach ($employees as $employee) {
            $userId = $employee->user_id;
            $userName = $employee->user->name ?? 'Unknown';

            $userSubmissionsMTD = KpiDailyEntry::where('user_id', $userId)
                ->whereBetween('entry_date', [$monthStart, $monthEnd])
                ->count();

            $userApprovedMTD = KpiDailyEntry::where('user_id', $userId)
                ->whereBetween('entry_date', [$monthStart, $monthEnd])
                ->where('status', 'approved')
                ->count();

            $userSubmissionToday = KpiDailyEntry::where('user_id', $userId)
                ->whereDate('entry_date', $today)
                ->exists();

            $userEntries = KpiEntryDetail::whereHas('kpiDailyEntry', function ($q) use ($userId, $monthStart, $monthEnd) {
                $q->where('user_id', $userId)
                  ->whereBetween('entry_date', [$monthStart, $monthEnd])
                  ->where('status', 'approved');
            })->with('kpiMetric')->get();

            $userTotalProgress = 0;
            $userValidEntries = 0;

            foreach ($userEntries as $entry) {
                if ($entry->kpiMetric && $entry->kpiMetric->target_value > 0) {
                    $progress = ($entry->value / $entry->kpiMetric->target_value) * 100;
                    $userTotalProgress += $progress;
                    $userValidEntries++;
                }
            }

            $userAvgPerformance = $userValidEntries > 0 ? round($userTotalProgress / $userValidEntries, 1) : 0;
            $userApprovalRate = $userSubmissionsMTD > 0 ? round(($userApprovedMTD / $userSubmissionsMTD) * 100, 1) : 0;

            $statusColor = 'text-gray-600';
            $statusText = 'No activity today';

            if ($userSubmissionToday) {
                if ($userAvgPerformance >= 90) {
                    $statusColor = 'text-green-600';
                    $statusText = 'Excellent performance';
                } elseif ($userAvgPerformance >= 75) {
                    $statusColor = 'text-yellow-600';
                    $statusText = 'Good performance';
                } else {
                    $statusColor = 'text-orange-600';
                    $statusText = 'Needs improvement';
                }
            }

            $individualStats[] = [
                'name' => $userName,
                'submissions_mtd' => $userSubmissionsMTD,
                'approved_mtd' => $userApprovedMTD,
                'approval_rate' => $userApprovalRate,
                'avg_performance' => $userAvgPerformance,
                'submitted_today' => $userSubmissionToday,
                'status_color' => $statusColor,
                'status_text' => $statusText,
            ];
        }

        usort($individualStats, function($a, $b) {
            if ($a['avg_performance'] == $b['avg_performance']) {
                return $b['submissions_mtd'] - $a['submissions_mtd'];
            }
            return $b['avg_performance'] <=> $a['avg_performance'];
        });

        return $individualStats;
    }
}