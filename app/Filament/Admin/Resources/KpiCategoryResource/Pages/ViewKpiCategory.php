<?php

namespace App\Filament\Admin\Resources\KpiCategoryResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Admin\Resources\KpiCategoryResource as ResourcesKpiCategoryResource;

class ViewKpiCategory extends ViewRecord
{

    protected static string $resource = ResourcesKpiCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('KPI Category Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('KPI Category Name'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->html()
                    ]),
            ]);
    }

}
