<?php

namespace App\Filament\App\Resources\TeamGoalsResource\Pages;

use App\Filament\App\Resources\TeamGoalsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeamGoals extends EditRecord
{
    protected static string $resource = TeamGoalsResource::class;

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
