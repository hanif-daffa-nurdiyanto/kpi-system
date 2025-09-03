<?php

namespace App\Filament\App\Resources\KpiMetricResource\Pages;

use App\Filament\App\Resources\KpiMetricResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKpiMetric extends EditRecord
{
    protected static string $resource = KpiMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
