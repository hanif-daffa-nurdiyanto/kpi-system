<?php

namespace App\Filament\App\Resources\KpiCategoryResource\Pages;

use App\Filament\App\Resources\KpiCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKpiCategory extends EditRecord
{
    protected static string $resource = KpiCategoryResource::class;

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
