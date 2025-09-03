<?php

namespace App\Filament\App\Resources\KpiDailyEntryResource\Pages;

use Filament\Actions;
use App\Models\Employee;
use App\Models\Department;
use App\Models\KpiDailyEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Builder;
use Filament\Resources\Components\Tab;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use App\Filament\App\Resources\KpiDailyEntryResource;
use App\Filament\App\Resources\KpiDailyEntryResource\Widgets\EmployeeDailyEntryOverview;

class ListKpiDailyEntries extends ListRecords
{
    protected static string $resource = KpiDailyEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make(),
            'Draft' => Tab::make()->query(fn ($query) => $query->where('status', 'draft')),
            'Submitted' => Tab::make()->query(fn ($query) => $query->where('status', 'submitted')),
            'Approved' => Tab::make()->query(fn ($query) => $query->where('status', 'approved')),
            'Rejected' => Tab::make()->query(fn ($query) => $query->where('status', 'rejected')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeDailyEntryOverview::class,
        ];
    }

}
