<?php

namespace App\Filament\Admin\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use App\Models\KpiMetric;
use Filament\Tables\Table;
use App\Models\KpiCategory;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\BooleanColumn;
use App\Filament\Admin\Resources\KpiMetricResource\Pages;
use App\Filament\Admin\Resources\KpiMetricResource\RelationManagers\KpiEntryDetailsRelationManager;

class KpiMetricResource extends Resource
{
    protected static ?string $model = KpiMetric::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'KPI Metrics';

    protected static ?string $navigationGroup = 'Performance Tracking';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Metric Information')
                    ->schema([
                        Select::make('kpi_category_id')
                            ->label('KPI Category')
                            ->options(KpiCategory::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        RichEditor::make('description')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Measurement Settings')
                    ->schema([
                        TextInput::make('unit')
                            ->label('Unit of Measurement')
                            ->placeholder('%, hours, count, etc.')
                            ->required()
                            ->maxLength(50),

                        TextInput::make('target_value')
                            ->label('Target Value')
                            ->numeric()
                            ->required(),

                        TextInput::make('weight')
                            ->label('Weight (Importance %)')
                            ->helperText('Value between 1-100 representing the importance of this metric')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100),

                        Toggle::make('is_higher_better')
                            ->label('Higher is Better')
                            ->helperText('Is a higher value better for this metric? (e.g., Sales: Yes, Errors: No)')
                            ->default(true),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('unit')
                    ->label('Unit')
                    ->sortable(),

                TextColumn::make('target_value')
                    ->label('Target')
                    ->sortable(),

                TextColumn::make('weight')
                    ->suffix('%')
                    ->sortable(),

                IconColumn::make('is_higher_better')
                    ->label('Goal Direction')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-trending-up')
                    ->falseIcon('heroicon-o-arrow-trending-down')
                    ->trueColor('success')
                    ->falseColor('danger'),

                BooleanColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                SelectFilter::make('kpi_category_id')
                    ->label('Category')
                    ->options(KpiCategory::pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('is_higher_better')
                    ->label('Goal Direction')
                    ->options([
                        '1' => 'Higher is Better',
                        '0' => 'Lower is Better',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('toggleActive')
                        ->label('Toggle Active Status')
                        ->icon('heroicon-o-power')
                        ->requiresConfirmation()
                        ->action(fn(Collection $records) => $records->each(fn($record) => $record->update(['is_active' => !$record->is_active]))),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            KpiEntryDetailsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKpiMetrics::route('/'),
            'create' => Pages\CreateKpiMetric::route('/create'),
            'view' => Pages\ViewKpiMetric::route('/{record}'),
            'edit' => Pages\EditKpiMetric::route('/{record}/edit'),
        ];
    }
}
