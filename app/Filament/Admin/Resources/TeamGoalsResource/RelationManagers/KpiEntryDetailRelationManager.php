<?php

namespace App\Filament\Admin\Resources\TeamGoalsResource\RelationManagers;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\KpiDailyEntry;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;

class KpiEntryDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'kpiEntryDetails';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('metric_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('metric_id')
            ->defaultSort('kpiDailyEntry.entry_date', 'desc')
            ->columns([
                TextColumn::make('kpiDailyEntry.user.name')
                    ->label('Employee'),
                TextColumn::make('kpiDailyEntry.entry_date')
                    ->date()
                    ->sortable()
                    ->label('Entry Date'),
                TextColumn::make('kpiDailyEntry.submitted_at')
                    ->date()
                    ->sortable()
                    ->label('Submitted'),
                TextColumn::make('kpiDailyEntry.status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'submitted',
                        'info' => 'reviewed',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('value')
                    ->sortable()
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Employee')
                    ->options(function () {
                        $departmentId = $this->ownerRecord->department_id;
            
                        return \App\Models\User::whereHas('employee.department', function (Builder $query) use ($departmentId) {
                            $query->where('id', $departmentId);
                        })
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->columnSpan(1),

                SelectFilter::make('status')
                    ->relationship('kpiDailyEntry', 'status')
                    ->options([
                        'gray' => 'draft',
                        'warning' => 'submitted',
                        'info' => 'reviewed',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->columnSpan(1),

                Filter::make('entry_date')
                    ->form([
                        Forms\Components\DatePicker::make('entry_from')
                            ->label('From')
                            ->columnSpan(1)
                            ->placeholder('Start Date'),
                        Forms\Components\DatePicker::make('entry_until')
                            ->label('Until')
                            ->columnSpan(1)
                            ->placeholder('End Date'),
                    ])
                    ->columns(2)
                    ->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['entry_from'],
                                fn(Builder $query, $date): Builder => $query->whereHas('kpiDailyEntry', fn($q) => $q->whereDate('entry_date', '>=', $date)),
                            )
                            ->when(
                                $data['entry_until'],
                                fn(Builder $query, $date): Builder => $query->whereHas('kpiDailyEntry', fn($q) => $q->whereDate('entry_date', '<=', $date)),
                            );
                    }),

                Filter::make('submitted')
                    ->label('Submitted Entries')
                    ->query(fn(Builder $query): Builder => $query->whereHas('kpiDailyEntry', fn($q) => $q->whereNotNull('submitted_at')))
                    ->columnSpan(1),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
