<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Departments';
    protected static ?string $pluralModelLabel = 'Departments';
    protected static ?string $modelLabel = 'Department';

    protected static ?string $navigationGroup = 'Team Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Department Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->maxLength(500)
                    ->columnSpanFull()
                    ->columns(1),

                Forms\Components\Select::make('manager_id')
                    ->label('Department Manager')
                    ->options(function () {
                        return \App\Models\User::role('Manager')
                            ->get()
                            ->mapWithKeys(function ($user) {
                                return [$user->id => $user->name . ' <span style="display: inline-block; margin-left: 8px; padding: 1px 6px; font-size: 0.60rem; background-color: rgba(59, 130, 246, 0.5); color: white; border-radius: 9999px;">Manager</span>'];
                            });
                    })
                    ->searchable()
                    ->preload()
                    ->allowHtml()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Department Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Manager')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('manager_id')
                    ->label('Filter by Manager')
                    ->relationship('manager', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'view' => Pages\ViewDepartment::route('/{record}'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
