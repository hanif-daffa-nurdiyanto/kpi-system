<?php

namespace App\Filament\App\Resources\DepartmentResource\Pages;

use App\Filament\App\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewDepartment extends ViewRecord
{
    protected static string $resource = DepartmentResource::class;

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
                Section::make('Department Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Department Name')
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('manager.name')
                                    ->label('Manager')
                                    ->icon('heroicon-o-user')
                                    ->color('primary')
                                    ->formatStateUsing(fn($state) => $state . ' (Manager)')
                            ]),

                        TextEntry::make('description')
                            ->label('Description')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Additional Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Group::make([
                                    TextEntry::make('created_at')
                                        ->label('Created on')
                                        ->dateTime('d M Y, H:i')
                                        ->icon('heroicon-o-calendar'),

                                    TextEntry::make('updated_at')
                                        ->label('Last updated')
                                        ->dateTime('d M Y, H:i')
                                        ->icon('heroicon-o-clock')
                                        ->color('gray')
                                ]),
                            ]),
                    ])
                    ->collapsed()
            ]);
    }
}
