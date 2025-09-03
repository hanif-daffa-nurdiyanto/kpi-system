<?php

namespace App\Filament\Admin\Resources\KpiDailyEntryResource\Pages;

use Dompdf\Options;
use Filament\Actions;
use App\Models\Employee;
use App\Models\Department;
use App\Models\KpiDailyEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\KpiDailyEntryResource;
use App\Filament\Admin\Resources\KpiDailyEntryResource as ResourcesKpiDailyEntryResource;
use App\Filament\Admin\Resources\KpiDailyEntryResource\Widgets\AdminDailyEntryOverview;

class ListKpiDailyEntries extends ListRecords
{
    protected static string $resource = ResourcesKpiDailyEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdminDailyEntryOverview::class,
        ];
    }
}
