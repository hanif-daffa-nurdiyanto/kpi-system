<?php

namespace App\Filament\Admin\Resources\KpiDailyEntryResource\Pages;

use App\Filament\Admin\Resources\KpiDailyEntryResource as ResourcesKpiDailyEntryResource;
use App\Filament\Resources\KpiDailyEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKpiDailyEntry extends CreateRecord
{
    protected static string $resource = ResourcesKpiDailyEntryResource::class;
}
