<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterSave(): void
    {
        $panelUserId = Role::where('name', 'panel_user')->value('id');

        $selectedRoles = $this->data['roles'] ?? [];

        if (!in_array($panelUserId, $selectedRoles)) {
            $selectedRoles[] = $panelUserId;
        }

        $this->record->roles()->sync($selectedRoles);
    }
}
