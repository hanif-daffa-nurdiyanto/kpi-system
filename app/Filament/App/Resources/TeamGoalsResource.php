<?php

namespace App\Filament\App\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\KpiMetric;
use App\Models\TeamGoals;
use App\Models\Department;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\TeamGoalsResource\Pages;
use App\Filament\App\Resources\TeamGoalsResource\RelationManagers;
use App\Filament\Admin\Resources\TeamGoalsResource\RelationManagers\KpiEntryDetailRelationManager;

class TeamGoalsResource extends Resource
{
    protected static ?string $model = TeamGoals::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Team Goals';

    protected static ?string $navigationGroup = 'Performance Tracking';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Team Goals Information')
                    ->description('Set targets for specific departments and metrics')
                    ->schema([
                        Forms\Components\Select::make('department_id')
                            ->label('Department')
                            ->options(Department::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('metric_id')
                            ->label('KPI Metric')
                            ->options(KpiMetric::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $metric = KpiMetric::find($state);
                                    if ($metric) {
                                        $set('metric_unit', $metric->unit);
                                    }
                                }
                            })
                            ->reactive()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('target_value')
                            ->label('Target Value')
                            ->required()
                            ->numeric()
                            ->suffix(function (callable $get) {
                                if ($get('metric_id')) {
                                    $metric = KpiMetric::find($get('metric_id'));
                                    return $metric ? $metric->unit : '';
                                }
                                return '';
                            })
                            ->columnSpan(1),

                        Forms\Components\Hidden::make('metric_unit'),
                    ])->columns(2),

                Section::make('Target Period')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->default(now())
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->default(now()->addMonth())
                            ->after('start_date')
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->helperText('Enable or disable this target')
                            ->default(true)
                            ->columnSpan(2),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('metric.name')
                    ->label('KPI Metric')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('target_value')
                    ->label('Target')
                    ->formatStateUsing(fn(string $state, TeamGoals $record): string =>
                        $state . ' ' . ($record->metric->unit ?? ''))
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function (TeamGoals $record): string {
                        $now = Carbon::now();
                        $startDate = Carbon::parse($record->start_date);
                        $endDate = Carbon::parse($record->end_date);

                        if (!$record->is_active) {
                            return 'disabled';
                        } elseif ($now->lt($startDate)) {
                            return 'not yet started';
                        } elseif ($now->gt($endDate)) {
                            return 'finished';
                        } else {
                            return 'running';
                        }
                    })
                    ->colors([
                        'danger' => 'disabled',
                        'warning' => 'not yet started',
                        'success' => 'running',
                        'secondary' => 'finished',
                    ]),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Filter Department')
                    ->options(Department::pluck('name', 'id'))
                    ->placeholder('All Departments'),

                SelectFilter::make('metric_id')
                    ->label('Filter KPI Metric')
                    ->options(KpiMetric::pluck('name', 'id'))
                    ->placeholder('All Metrics'),

                Filter::make('is_active')
                    ->label('Active Traget Only')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true))
                    ->toggle(),

                Filter::make('current')
                    ->label('Target Period Currently In')
                    ->query(function (Builder $query): Builder {
                        return $query
                            ->where('start_date', '<=', now())
                            ->where('end_date', '>=', now())
                            ->where('is_active', true);
                    })
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('toggleActive')
                        ->label('Enable/Disable')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'is_active' => !$record->is_active,
                                ]);
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            KpiEntryDetailRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeamGoals::route('/'),
            'create' => Pages\CreateTeamGoals::route('/create'),
            'view' => Pages\ViewTeamGoals::route('/{record}'),
            'edit' => Pages\EditTeamGoals::route('/{record}/edit'),
        ];
    }
}