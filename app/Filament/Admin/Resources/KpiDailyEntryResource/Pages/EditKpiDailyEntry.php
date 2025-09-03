<?php

namespace App\Filament\Admin\Resources\KpiDailyEntryResource\Pages;

use App\Filament\Admin\Resources\KpiDailyEntryResource as ResourcesKpiDailyEntryResource;
use App\Filament\Resources\KpiDailyEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKpiDailyEntry extends EditRecord
{
    protected static string $resource = ResourcesKpiDailyEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
