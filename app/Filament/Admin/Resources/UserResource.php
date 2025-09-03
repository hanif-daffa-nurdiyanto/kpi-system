<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\Pages\CreateUser;
use Filament\Forms\Components\Select;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\DateTimePicker::make('email_verified_at'),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(Page $livewire) => ($livewire instanceof CreateUser))
                    ->maxLength(255),

                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        return collect($record?->roles ?? [])
                            ->reject(fn($role) => $role->name === 'panel_user')
                            ->pluck('id')
                            ->toArray();
                    })
                    ->options(fn() => Role::query()
                        ->whereNotIn('name', ['panel_user', 'super_admin'])
                        ->pluck('name', 'id')
                        ->toArray())
                ]);
        }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->formatStateUsing(fn($state) => collect($state)->reject(fn($role) => $role === 'panel_user')->implode(',')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->badge()
                    ->requiresConfirmation()
                    ->size('xl')
                    ->icon('heroicon-o-check')
                    ->label('')
                    ->color('success')
                    ->modalHeading('Approved User')
                    ->modalSubheading('Are you sure you would like to do this?')
                    ->modalButton('Confirm')
                    ->modalIcon('heroicon-o-check')
                    ->modalWidth('md')
                    ->form([
                        Select::make('roles')
                            ->label(__('Assign Roles'))
                            ->preload()
                            ->searchable()
                            ->options(fn() => Role::all()
                                ->pluck('name')
                                ->reject(fn($role) => in_array($role, ['panel_user', 'super_admin']))
                                ->mapWithKeys(fn($name) => [$name => $name])
                                ->toArray())
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->roles()->detach();
                        $record->assignRole('panel_user');
                        if (!empty($data['roles'])) {
                            $record->assignRole($data['roles']);
                        }
                    })
                    ->visible(fn($record) => !$record->hasRole('panel_user')),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-x-mark')
                    ->iconButton()
                    ->label('')
                    ->badge()
                    ->size('xl')
                    ->visible(fn($record) => $record->roles->isEmpty()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('Delete')
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                try {
                                    $record->delete();
                                } catch (\Throwable $e) {
                                    Notification::make()
                                        ->title('Failed To Delete')
                                        ->body("Failed to delete user {$record->name} because it is still used in other tables.")
                                        ->danger()
                                        ->send();

                                    return;
                                }
                            }

                            Notification::make()
                                ->title('Successfully Deleted')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}
