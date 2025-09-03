<?php

namespace App\Filament\App\Resources\KpiCategoryResource\Pages;

use App\Filament\App\Resources\KpiCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKpiCategories extends ListRecords
{
    protected static string $resource = KpiCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
