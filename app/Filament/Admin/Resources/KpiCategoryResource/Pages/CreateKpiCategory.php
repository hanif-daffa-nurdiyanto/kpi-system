<?php

namespace App\Filament\Admin\Resources\KpiCategoryResource\Pages;

use App\Filament\Admin\Resources\KpiCategoryResource as ResourcesKpiCategoryResource;
use App\Filament\Resources\KpiCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKpiCategory extends CreateRecord
{
    protected static string $resource = ResourcesKpiCategoryResource::class;
}
