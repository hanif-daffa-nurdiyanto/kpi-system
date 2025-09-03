<?php

namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use App\Filament\Resources\EmployeeResource;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Admin\Resources\EmployeeResource as ResourcesEmployeeResource;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = ResourcesEmployeeResource::class;

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
                Section::make('Employee Information')
                    ->schema([
                        Section::make('Employee')
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Name'),
                                TextEntry::make('phone')
                                    ->label('Phone Number'),
                                TextEntry::make('user.email')
                                    ->label('Email')
                                    ->columnSpanFull(),
                            ])->columnSpan(1)
                            ->columns(2),
                        Section::make('Department')
                            ->schema([
                                TextEntry::make('department.name')
                                    ->badge(),
                                TextEntry::make('department.manager.name')
                                    ->label('Manager Department')
                                    ->icon('heroicon-o-user')
                                    ->formatStateUsing(fn ($state) => $state . ' (Manager)')
                                    ->color('info'),
                                TextEntry::make('department.description')
                                ->label('Description')
                            ])->columnSpan(1)
                            ->columns(2),
                    ])->columns(2),
            ]);
    }
}
