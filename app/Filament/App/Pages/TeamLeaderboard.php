<?php

namespace App\Filament\App\Pages;

use App\Models\Department;
use App\Models\Employee;
use App\Models\KpiDailyEntry;
use App\Models\KpiEntryDetail;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TeamLeaderboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static string $view = 'filament.app.pages.team-leaderboard';
    protected static ?string $navigationLabel = 'Team Leaderboard';
    protected static ?string $title = 'Team Performance Leaderboard';
    protected static ?int $navigationSort = 2;

    public array $teamStats = [];
    public array $individualStats = [];
    public string $departmentName = '';
    public array $periodOptions = [];
    public string $selectedPeriod = 'mtd';
    public Carbon $startDate;
    public Carbon $endDate;

    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->hasRole('employee');
    }

    public function mount(): void
    {
        $this->setupPeriods();
        $this->generateStats();
    }

    protected function setupPeriods(): void
    {
        $this->periodOptions = [
            'today' => 'Today',
            'week' => 'This Week',
            'mtd' => 'Month to Date',
            'last_month' => 'Last Month',
            'quarter' => 'This Quarter',
            'ytd' => 'Year to Date'
        ];

        $this->setPeriodDates();
    }

    public function updatedSelectedPeriod(): void
    {
        $this->setPeriodDates();
        $this->generateStats();
    }

    protected function setPeriodDates(): void
    {
        $now = Carbon::now();

        switch ($this->selectedPeriod) {
            case 'today':
                $this->startDate = $now->copy()->startOfDay();
                $this->endDate = $now->copy()->endOfDay();
                break;
            case 'week':
                $this->startDate = $now->copy()->startOfWeek();
                $this->endDate = $now->copy()->endOfWeek();
                break;
            case 'mtd':
                $this->startDate = $now->copy()->startOfMonth();
                $this->endDate = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $this->startDate = $now->copy()->subMonth()->startOfMonth();
                $this->endDate = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'quarter':
                $this->startDate = $now->copy()->startOfQuarter();
                $this->endDate = $now->copy()->endOfQuarter();
                break;
            case 'ytd':
                $this->startDate = $now->copy()->startOfYear();
                $this->endDate = $now->copy()->endOfYear();
                break;
            default:
                $this->startDate = $now->copy()->startOfMonth();
                $this->endDate = $now->copy()->endOfMonth();
        }
    }

    protected function generateStats(): void
    {
        $user = Auth::user();

        // Check if user is manager or regular employee
        $department = Department::where('manager_id', $user->id)->first();

        if (!$department) {
            // If not manager, get department from employee record
            $employee = Employee::where('user_id', $user->id)->with('department')->first();
            $department = $employee?->department;
        }

        if (!$department) {
            $this->teamStats = [];
            $this->individualStats = [];
            return;
        }

        $this->departmentName = $department->name;

        // Get all employees in this department
        $employees = Employee::where('department_id', $department->id)
            ->with('user')
            ->get();

        $employeeIds = $employees->pluck('user_id')->toArray();

        // Generate Team Stats
        $this->teamStats = $this->generateTeamStats($employeeIds, $employees->count());

        // Generate Individual Stats for leaderboard
        $this->individualStats = $this->generateIndividualStats($employees);
    }

    protected function generateTeamStats(array $employeeIds, int $totalEmployees): array
    {
        $today = Carbon::today();

        // Total submissions for period
        $totalSubmissions = KpiDailyEntry::whereIn('user_id', $employeeIds)
            ->whereBetween('entry_date', [$this->startDate, $this->endDate])
            ->count();

        // Approved submissions for period
        $approvedSubmissions = KpiDailyEntry::whereIn('user_id', $employeeIds)
            ->whereBetween('entry_date', [$this->startDate, $this->endDate])
            ->where('status', 'approved')
            ->count();

        // Today's submissions (regardless of selected period)
        $submissionsToday = KpiDailyEntry::whereIn('user_id', $employeeIds)
            ->whereDate('entry_date', $today)
            ->count();

        // Pending approvals
        $pendingApprovals = KpiDailyEntry::whereIn('user_id', $employeeIds)
            ->where('status', 'pending')
            ->count();

        // Calculate team average performance
        $entries = KpiEntryDetail::whereHas('kpiDailyEntry', function ($q) use ($employeeIds) {
            $q->whereIn('user_id', $employeeIds)
              ->whereBetween('entry_date', [$this->startDate, $this->endDate])
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
        $approvalRate = $totalSubmissions > 0 ? round(($approvedSubmissions / $totalSubmissions) * 100, 1) : 0;

        // Active participants (employees who submitted at least once in period)
        $activeParticipants = KpiDailyEntry::whereIn('user_id', $employeeIds)
            ->whereBetween('entry_date', [$this->startDate, $this->endDate])
            ->distinct('user_id')
            ->count();

        $participationRate = $totalEmployees > 0 ? round(($activeParticipants / $totalEmployees) * 100, 1) : 0;

        return [
            [
                'title' => 'Team Members',
                'value' => $totalEmployees,
                'subtitle' => "Active: $activeParticipants",
                'icon' => 'heroicon-o-user-group',
                'color' => 'text-blue-600 dark:text-blue-400',
                'description' => "Participation: {$participationRate}%",
            ],
            [
                'title' => 'Total Submissions',
                'value' => $totalSubmissions,
                'subtitle' => "Approved: $approvedSubmissions",
                'icon' => 'heroicon-o-document-text',
                'color' => 'text-indigo-600 dark:text-indigo-400',
                'description' => "Approval Rate: {$approvalRate}%",
            ],
            [
                'title' => 'Today\'s Activity',
                'value' => $submissionsToday,
                'subtitle' => "Pending: $pendingApprovals",
                'icon' => 'heroicon-o-clock',
                'color' => $submissionsToday > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400',
                'description' => 'Real-time activity',
            ],
            [
                'title' => 'Team Performance',
                'value' => $avgPerformance . '%',
                'icon' => 'heroicon-o-chart-bar',
                'color' => $avgPerformance >= 90 ? 'text-green-600 dark:text-green-400' : ($avgPerformance >= 75 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400'),
                'description' => 'Average performance score',
            ],
        ];
    }

    protected function generateIndividualStats($employees): array
    {
        $individualStats = [];
        $today = Carbon::today();

        foreach ($employees as $employee) {
            $userId = $employee->user_id;
            $userName = $employee->user->name ?? 'Unknown';
            $userEmail = $employee->user->email ?? '';

            // Individual submissions for period
            $userSubmissions = KpiDailyEntry::where('user_id', $userId)
                ->whereBetween('entry_date', [$this->startDate, $this->endDate])
                ->count();

            // Individual approved submissions for period
            $userApproved = KpiDailyEntry::where('user_id', $userId)
                ->whereBetween('entry_date', [$this->startDate, $this->endDate])
                ->where('status', 'approved')
                ->count();

            // Today's submission status
            $userSubmissionToday = KpiDailyEntry::where('user_id', $userId)
                ->whereDate('entry_date', $today)
                ->exists();

            // Streak calculation (consecutive days with submissions)
            $streak = $this->calculateStreak($userId);

            // Individual performance for period
            $userEntries = KpiEntryDetail::whereHas('kpiDailyEntry', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->whereBetween('entry_date', [$this->startDate, $this->endDate])
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
            $userApprovalRate = $userSubmissions > 0 ? round(($userApproved / $userSubmissions) * 100, 1) : 0;

            // Calculate consistency score (submissions frequency)
            $workingDays = $this->getWorkingDaysInPeriod();
            $consistencyScore = $workingDays > 0 ? round(($userSubmissions / $workingDays) * 100, 1) : 0;

            // Overall score calculation (weighted)
            $overallScore = round(
                ($userAvgPerformance * 0.4) +
                ($userApprovalRate * 0.3) +
                ($consistencyScore * 0.3)
            , 1);

            // Determine rank badge
            $rankBadge = $this->getRankBadge($overallScore);

            // Determine status
            $status = $this->getEmployeeStatus($userSubmissionToday, $userAvgPerformance, $streak);

            $individualStats[] = [
                'name' => $userName,
                'email' => $userEmail,
                'submissions' => $userSubmissions,
                'approved' => $userApproved,
                'approval_rate' => $userApprovalRate,
                'avg_performance' => $userAvgPerformance,
                'consistency_score' => $consistencyScore,
                'overall_score' => $overallScore,
                'streak' => $streak,
                'submitted_today' => $userSubmissionToday,
                'status' => $status,
                'rank_badge' => $rankBadge,
            ];
        }

        // Sort by overall score descending, then by performance
        usort($individualStats, function($a, $b) {
            if ($a['overall_score'] == $b['overall_score']) {
                return $b['avg_performance'] <=> $a['avg_performance'];
            }
            return $b['overall_score'] <=> $a['overall_score'];
        });

        // Add rank position
        foreach ($individualStats as $index => &$stat) {
            $stat['rank'] = $index + 1;
        }

        return $individualStats;
    }

    protected function calculateStreak(int $userId): int
    {
        $streak = 0;
        $currentDate = Carbon::yesterday(); // Start from yesterday to avoid today affecting streak

        while ($currentDate->diffInDays(Carbon::now()->subMonths(3)) < 90) { // Max 3 months back
            $hasSubmission = KpiDailyEntry::where('user_id', $userId)
                ->whereDate('entry_date', $currentDate)
                ->exists();

            if ($hasSubmission) {
                $streak++;
                $currentDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    protected function getWorkingDaysInPeriod(): int
    {
        $workingDays = 0;
        $current = $this->startDate->copy();

        while ($current->lte($this->endDate)) {
            if ($current->isWeekday()) { // Monday to Friday
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }

    protected function getRankBadge(float $score): array
    {
        if ($score >= 95) {
            return ['label' => 'Champion', 'color' => 'bg-gradient-to-r from-yellow-400 to-yellow-600', 'icon' => 'heroicon-s-star'];
        } elseif ($score >= 90) {
            return ['label' => 'Expert', 'color' => 'bg-gradient-to-r from-purple-400 to-purple-600', 'icon' => 'heroicon-s-sparkles'];
        } elseif ($score >= 80) {
            return ['label' => 'Proficient', 'color' => 'bg-gradient-to-r from-blue-400 to-blue-600', 'icon' => 'heroicon-s-trophy'];
        } elseif ($score >= 70) {
            return ['label' => 'Developing', 'color' => 'bg-gradient-to-r from-green-400 to-green-600', 'icon' => 'heroicon-s-arrow-trending-up'];
        } else {
            return ['label' => 'Starter', 'color' => 'bg-gradient-to-r from-gray-400 to-gray-600', 'icon' => 'heroicon-s-academic-cap'];
        }
    }

    protected function getEmployeeStatus(bool $submittedToday, float $performance, int $streak): array
    {
        if ($submittedToday) {
            if ($performance >= 90) {
                return ['text' => 'Outstanding', 'color' => 'text-green-600 dark:text-green-400'];
            } elseif ($performance >= 75) {
                return ['text' => 'Good', 'color' => 'text-blue-600 dark:text-blue-400'];
            } else {
                return ['text' => 'Needs Focus', 'color' => 'text-yellow-600 dark:text-yellow-400'];
            }
        } else {
            if ($streak > 0) {
                return ['text' => 'Streak Active', 'color' => 'text-orange-600 dark:text-orange-400'];
            } else {
                return ['text' => 'Inactive Today', 'color' => 'text-gray-600 dark:text-gray-400'];
            }
        }
    }

    public function getPeriodLabel(): string
    {
        return $this->periodOptions[$this->selectedPeriod] ?? 'Month to Date';
    }

    public function getDateRangeLabel(): string
    {
        return $this->startDate->format('M j') . ' - ' . $this->endDate->format('M j, Y');
    }
}