<?php

namespace App\Filament\Admin\Resources\KpiCategoryResource\Pages;

use App\Filament\Admin\Resources\KpiCategoryResource as ResourcesKpiCategoryResource;
use App\Filament\Resources\KpiCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKpiCategories extends ListRecords
{
    protected static string $resource = ResourcesKpiCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
