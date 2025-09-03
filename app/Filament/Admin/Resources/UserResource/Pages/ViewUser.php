<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Admin\Resources\UserResource;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->action(function ($record) {
                    try {
                        $record->delete();

                        Notification::make()
                            ->title('Successfully Deleted')
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.users.index');
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed To Delete')
                            ->body("Data {$record->name} failed to be deleted because it is still listed in the employee table.")
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->color('danger')
        ];
    }
}
