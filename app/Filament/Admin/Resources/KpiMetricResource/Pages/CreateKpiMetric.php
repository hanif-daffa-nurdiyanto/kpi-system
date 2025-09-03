<?php

namespace App\Filament\Admin\Resources\KpiMetricResource\Pages;

use App\Filament\Admin\Resources\KpiMetricResource as ResourcesKpiMetricResource;
use App\Filament\Resources\KpiMetricResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKpiMetric extends CreateRecord
{
    protected static string $resource = ResourcesKpiMetricResource::class;
}
