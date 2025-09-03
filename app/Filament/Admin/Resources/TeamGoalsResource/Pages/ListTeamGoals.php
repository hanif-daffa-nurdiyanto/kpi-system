<?php

namespace App\Filament\Admin\Resources\TeamGoalsResource\Pages;

use App\Filament\Admin\Resources\TeamGoalsResource;
use App\Filament\Admin\Resources\TeamGoalsResource\Widgets\TeamGoalsOverview as WidgetsTeamGoalsOverview;
use App\Filament\Resources\TeamGoalsResource\Widgets\TeamGoalsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeamGoals extends ListRecords
{
    protected static string $resource = TeamGoalsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Team Goal'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WidgetsTeamGoalsOverview::class,
        ];
    }
}
