<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\KpiCategory;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\BooleanColumn;
use App\Filament\Admin\Resources\KpiCategoryResource\Pages;
use App\Filament\Admin\Resources\KpiCategoryResource\RelationManagers\KpiMetricsRelationManager;

class KpiCategoryResource extends Resource
{
    protected static ?string $model = KpiCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'KPI Categories';

    protected static ?string $navigationGroup = 'Performance Tracking';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('description')
                    ->maxLength(500),
                Forms\Components\CheckboxList::make('applicable_roles')
                    ->options(Role::all()->pluck('name', 'name')->toArray())
                    ->required()
                    ->hidden()
                    ->dehydratedWhenHidden()
                    ->default(fn () => Role::all()->pluck('name','name')->toArray()),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->label('Active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->formatStateUsing(fn($state) => strip_tags($state))
                    ->limit(50),
                TextColumn::make('applicable_roles')
                    ->label('Roles'),
                BooleanColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Filter by Status')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            KpiMetricsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKpiCategories::route('/'),
            'create' => Pages\CreateKpiCategory::route('/create'),
            'view' => Pages\ViewKpiCategory::route('/{record}'),
            'edit' => Pages\EditKpiCategory::route('/{record}/edit'),
        ];
    }
}
