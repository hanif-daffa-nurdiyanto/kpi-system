<?php

namespace App\Filament\Admin\Resources\KpiMetricResource\Pages;

use App\Filament\Admin\Resources\KpiMetricResource as ResourcesKpiMetricResource;
use App\Filament\Resources\KpiMetricResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKpiMetrics extends ListRecords
{
    protected static string $resource = ResourcesKpiMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
