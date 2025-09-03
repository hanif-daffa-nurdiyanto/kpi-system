<?php

namespace App\Filament\Widgets;

use App\Models\KpiDailyEntry;
use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\KpiStatsService;
use Livewire\Attributes\On;

class StatDashboard extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected static ?int $sort = 3;

    public ?int $selectedDepartmentId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    #[On('departmentSelected')]
    public function updateSelectedDepartment($departmentId = null): void
    {
        $this->selectedDepartmentId = $departmentId !== null && $departmentId !== ''
            ? (int) $departmentId
            : null;
    }
    #[On('startDate')]
    public function updateStartDate($date): void
    {
        $this->startDate = $date;
    }
    #[On('endDate')]
    public function updateEndDate($date): void
    {
        $this->endDate = $date;
    }
    public function mount(): void
    {
        $this->selectedDepartmentId = null;
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $statsService = app(KpiStatsService::class);
        if ($user->hasRole('super_admin')) {
            return $this->getSuperAdminStats($statsService);
        }
        elseif ($user->hasRole('manager')) {
            return $this->getManagerStats($statsService);
        }
        else {
            return $this->getUserStats($statsService);
        }
    }



    private function getSuperAdminStats(KpiStatsService $statsService): array
    {
        $departmentId = $this->selectedDepartmentId;
        $selectedDepartment = $departmentId ? Department::find($departmentId) : null;
        $entriesThisMonth = $statsService->getEntriesCount($departmentId, false, $this->startDate, $this->endDate);
        $entriesLastMonth = $statsService->getEntriesCount($departmentId, true, $this->startDate, $this->endDate);
        $percentChange = $statsService->calculatePercentChange($entriesThisMonth, $entriesLastMonth);
        $totalUsers = $departmentId
            ? User::whereHas('employee', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })->count()
            : User::count();
        $completionRate = $statsService->getCompletionRate($departmentId, $this->startDate, $this->endDate);
        $departments = Department::count();
        return [
            Stat::make($departmentId ? 'Department Entries This Month' : 'Total Entries This Month', $entriesThisMonth)
                ->description($percentChange > 0 ? "+{$percentChange}%" : "{$percentChange}%")
                ->descriptionIcon($percentChange > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($percentChange > 0 ? 'success' : 'danger'),

            Stat::make($departmentId ? 'Department Users' : 'Total Users', $totalUsers)
                ->description($departmentId ? ($selectedDepartment ? $selectedDepartment->name : 'Selected department') : 'Across all departments'),

            Stat::make('Completion Rate', "{$completionRate}%")
                ->description('Based on expected daily entries')
                ->color($completionRate > 80 ? 'success' : ($completionRate > 50 ? 'warning' : 'danger')),

            Stat::make('Total Departments', $departments)
                ->description('Active departments')
                ->color('primary'),
        ];
    }



    private function getManagerStats(KpiStatsService $statsService): array
    {
        $user = Auth::user();
        $department = Department::where('manager_id', $user->id)->first();
        if (!$department) {
            return [
                Stat::make('Department Error', 'No department assigned')
                    ->description('Please contact an administrator')
                    ->color('danger'),
            ];
        }
        $departmentId = $department->id;
        $entriesThisMonth = $statsService->getEntriesCount($departmentId, false, $this->startDate, $this->endDate);
        $entriesLastMonth = $statsService->getEntriesCount($departmentId, true, $this->startDate, $this->endDate);
        $percentChange = $statsService->calculatePercentChange($entriesThisMonth, $entriesLastMonth);
        $departmentUsers = Employee::where('department_id', $departmentId)->count();
        $completionRate = $statsService->getCompletionRate($departmentId, $this->startDate, $this->endDate);
        $pendingEntriesQuery = KpiDailyEntry::whereHas('user.employee', function ($query) use ($departmentId) {
            $query->where('department_id', $departmentId);
        })
            ->where('status', 'submitted');
        if ($this->startDate && $this->endDate) {
            $pendingEntriesQuery->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        }
        $pendingEntries = $pendingEntriesQuery->count();
        return [
            Stat::make('Team Entries This Month', $entriesThisMonth)
                ->description($percentChange > 0 ? "+{$percentChange}%" : "{$percentChange}%")
                ->descriptionIcon($percentChange > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($percentChange > 0 ? 'success' : 'danger'),

            Stat::make('Team Members', $departmentUsers)
                ->description($department->name ?? 'Department')
                ->color('primary'),

            Stat::make('Completion Rate', "{$completionRate}%")
                ->description('Based on expected daily entries')
                ->color($completionRate > 80 ? 'success' : ($completionRate > 50 ? 'warning' : 'danger')),

            Stat::make('Pending Approvals', $pendingEntries)
                ->description('Entries awaiting review')
                ->color($pendingEntries > 0 ? 'warning' : 'success'),
        ];
    }




    private function getUserStats(KpiStatsService $statsService): array
    {
        $user = Auth::user();
        $query = KpiDailyEntry::where('user_id', $user->id);
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        }
        $entriesThisMonth = $query->count();
        $lastEntry = KpiDailyEntry::where('user_id', $user->id)->orderBy('entry_date', 'desc')->first();
        $daysSinceLastEntry = $lastEntry
            ? Carbon::parse($lastEntry->entry_date)->diffInDays(Carbon::now())
            : null;
        return [
            Stat::make('Your Entries', $entriesThisMonth)
                ->description($this->startDate && $this->endDate ? "Filtered from $this->startDate to $this->endDate" : "This Month"),

            Stat::make('Last Entry', $lastEntry ? Carbon::parse($lastEntry->entry_date)->format('M d, Y') : 'No entries yet')
                ->description($lastEntry ? 'Status: ' . ucfirst($lastEntry->status) : 'Create your first entry')
                ->color($statsService->getStatusColor($lastEntry->status ?? null)),

            Stat::make('Days Since Last Entry', $daysSinceLastEntry ?? 'N/A')
                ->description($lastEntry ? (Carbon::parse($lastEntry->entry_date)->isToday() ? 'Updated today' : '') : 'No entries yet')
                ->color($statsService->getDaysColor($daysSinceLastEntry)),
        ];
    }
}
