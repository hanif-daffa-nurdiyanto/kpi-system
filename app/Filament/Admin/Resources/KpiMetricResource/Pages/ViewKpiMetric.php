<?php

namespace App\Filament\Admin\Resources\KpiMetricResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\KpiMetricResource;
use App\Filament\Admin\Resources\KpiMetricResource as ResourcesKpiMetricResource;

class ViewKpiMetric extends ViewRecord
{
    protected static string $resource = ResourcesKpiMetricResource::class;

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
                Section::make('KPI Metric Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('KPI Metric Name'),
                        TextEntry::make('unit')
                            ->badge(),
                        TextEntry::make('target_value')
                            ->label('Target Value'),
                        TextEntry::make('weight'),
                        TextEntry::make('is_active')
                            ->label('Active')
                            ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                            ->iconColor(fn ($state) => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Non-Active')
                        ,
                        TextEntry::make('description')
                            ->label('Description')
                            ->html()
                            ->columnSpanFull(),
                    ])->columns(5),
            ]);
    }
}
