<?php

namespace App\Filament\Admin\Widgets;

use App\Models\KpiDailyEntry;
use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\KpiStatsService;
use Livewire\Attributes\On;

class AdminStatsOverview extends Widget
{
    protected static string $view = 'filament.admin.widgets.admin-stats-overview';

    // Konfigurasi untuk full width
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 1;

    // Hapus atau nonaktifkan polling jika tidak diperlukan
    // protected static ?string $pollingInterval = '60s';

    // Pastikan tidak ada batasan height
    protected static bool $isLazy = false;

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

    public function getStatsData(): array
    {
        $user = Auth::user();
        $statsService = app(KpiStatsService::class);

        if (!$user->hasRole('super_admin')) {
            return [];
        }

        return $statsService->getAdminOverviewStats(
            $this->selectedDepartmentId,
            $this->startDate,
            $this->endDate
        );
    }

    // Override method untuk memastikan widget menggunakan full container
    // protected function getViewData(): array
    // {
    //     return array_merge(parent::getViewData(), [
    //         'columnSpan' => 'full',
    //     ]);
    // }
}