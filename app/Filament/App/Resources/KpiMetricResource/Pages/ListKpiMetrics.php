<?php

namespace App\Filament\App\Resources\KpiMetricResource\Pages;

use App\Filament\App\Resources\KpiMetricResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKpiMetrics extends ListRecords
{
    protected static string $resource = KpiMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
