<?php

namespace App\Filament\App\Resources\TeamGoalsResource\Pages;

use Filament\Actions;
use App\Models\KpiEntryDetail;
use Illuminate\Support\Carbon;
use Filament\Infolists\Infolist;
use App\Services\TeamGoalService;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Tabs;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\App\Resources\TeamGoalsResource;
use App\Filament\Admin\Resources\TeamGoalsResource\Widgets\HistoricalPerformanceChart;
use App\Filament\App\Resources\KpiMetricResource\RelationManagers\KpiEntryDetailsRelationManager;
use App\Filament\App\Resources\TeamGoalsResource\Widgets\TrendChartManager;
use Filament\Notifications\Notification;

class ViewTeamGoals extends ViewRecord
{
    protected static string $resource = TeamGoalsResource::class;

    protected TeamGoalService $teamGoalService;

    public function boot(TeamGoalService $teamGoalService)
    {
        $this->teamGoalService = $teamGoalService;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TrendChartManager::class,
            HistoricalPerformanceChart::class
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Team Goal'),

            Actions\Action::make('toggleActive')
                ->label(fn($record) => $record->is_active ? 'Deactivate Target' : 'Activate Target')
                ->icon(fn($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn($record) => $record->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'is_active' => !$this->record->is_active
                    ]);

                    $this->refreshFormData([
                        'is_active',
                    ]);

                    Notification::make()
                    ->title($this->record->is_active ? 'Target has been activated' : 'Target has been deactivated')
                    ->success()
                    ->send();
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        // Get all performance data from the service
        $performanceData = $this->teamGoalService->getPerformanceData($this->record);

        return $infolist->schema([
            $this->getTargetOverviewSection($performanceData),
            // $this->getInfoGrid($performanceData),
            $this->getAdditionalInfoTabs($performanceData),
        ])
            ->columns(2);
    }

    protected function getTargetOverviewSection($data): Section
    {
        return Section::make('Target Overview')
            ->collapsible()
            ->schema([
                Section::make('Department Information')
                    ->schema([
                        TextEntry::make('department.name')
                            ->label('Department')
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('department.manager.name')
                            ->label('Department Manager')
                            ->icon('heroicon-o-user')
                            ->color('primary')
                            ->formatStateUsing(fn($state) => $state . ' (Manager)')
                            ->visible(fn() => $this->record->department->manager()->exists()),

                        TextEntry::make('employees_count')
                            ->label('Team Size')
                            ->state($data['employeesCount'])
                            ->formatStateUsing(fn($state): string =>
                                "{$state} employees"),

                        TextEntry::make('department.description')
                            ->label('Department Description')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(1),

                Section::make('Target Information')
                    ->schema([

                        TextEntry::make('metric.name')
                            ->label('KPI Metric')
                            ->size('lg')
                            ->weight('bold'),

                        Grid::make()
                            ->schema([
                                TextEntry::make('target_value')
                                    ->label('Target Value')
                                    ->formatStateUsing(fn(string $state): string =>
                                        "{$state} {$data['unit']}")
                                    ->size('lg')
                                    ->weight('bold'),

                                TextEntry::make('current_value')
                                    ->label('Current Performance')
                                    ->state($data['currentValue'])
                                    ->formatStateUsing(fn($state): string =>
                                        number_format($state, 2) . " {$data['unit']}")
                                    ->size('lg')
                                    ->color($this->getColorForPercentage($data['achievementPercentage'])),

                                TextEntry::make('achievement')
                                    ->label('Achievement')
                                    ->state($data['achievementPercentage'])
                                    ->formatStateUsing(fn($state): string =>
                                        number_format($state, 1) . "%")
                                    ->size('lg')
                                    ->color($this->getColorForPercentage($data['achievementPercentage'])),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->state($data['status'])
                                    ->badge()
                                    ->color($data['statusColor'])
                                    ->size('lg'),
                            ])->columns(2),
                    ])->columnSpan(1)
            ])
            ->columns(2);
    }

    protected function getAdditionalInfoTabs($data): Tabs
    {
        return Tabs::make('Additional Information')
            ->tabs([
                // $this->getDepartmentInfoTab($data),
                // $this->getHistoricalPerformanceTab($data),
                $this->getTimelineTab($data),
                $this->getPerformanceDetailsTab($data),
                $this->getRecommendationsTab($data),
            ])
            ->columnSpanFull();

    }

    protected function getTimelineTab($data)
    {
        return Tabs\Tab::make('Timeline Information')
            ->schema([
                Grid::make()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('start_date')
                            ->label('Start Date')
                            ->date('d M Y')
                            ->columnSpan(1),

                        TextEntry::make('end_date')
                            ->label('End Date')
                            ->date('d M Y')
                            ->columnSpan(3),
                    ]),

                TextEntry::make('progress')
                    ->label('Time Progress')
                    ->state($data['progressPercentage'])
                    ->formatStateUsing(fn($state): string =>
                        number_format($state, 1) . "%")
                    ->placeholder($this->getProgressPlaceholder($data)),

                TextEntry::make('is_active')
                    ->label('Active Status')
                    ->formatStateUsing(fn(bool $state): string =>
                        $state ? 'Active' : 'Inactive')
                    ->badge()
                    ->icon(fn($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn(bool $state): string =>
                        $state ? 'success' : 'danger'),
            ]);
    }

    protected function getProgressPlaceholder($data): string
    {
        $now = now();

        if ($now->lt($data['startDate'])) {
            return "Target has not started yet";
        }

        if ($now->gt($data['endDate'])) {
            return "Target period has ended";
        }

        return "Day {$data['passedDays']} of {$data['totalDays']}";
    }

    protected function getPerformanceDetailsTab($data)
    {
        return Tabs\Tab::make('Performance Details')
            ->schema([
                TextEntry::make('category.description')
                    ->state($this->record->metric->category->description ?? '')
                    ->label('KPI Category')
                    ->html()
                    ->visible(fn($state) => !empty($state)),

                TextEntry::make('optimality')
                    ->label('Optimization Direction')
                    ->state($data['isHigherBetter'] ? 'Higher value is better' : 'Lower value is better')
                    ->badge()
                    ->color($data['isHigherBetter'] ? 'success' : 'info'),

                TextEntry::make('metric.description')
                    ->label('Metric Description')
                    ->html(),
            ])
            ->columns(3);
    }

    protected function getRecommendationsTab($data)
    {
        return Tabs\Tab::make('Recommendations')
            ->schema([
                TextEntry::make('recommendations')
                    ->label('Performance Recommendations')
                    ->state(
                        $this->teamGoalService->getRecommendations(
                            $data['achievementPercentage'],
                            $data['isHigherBetter'],
                            $data['currentValue'],
                            $data['targetValue'],
                            $data['unit']
                        )
                    )
                    ->markdown(),
            ]);
    }

    protected function getColorForPercentage($percentage): string
    {
        if ($percentage >= 90)
            return 'success';
        if ($percentage >= 70)
            return 'warning';
        return 'danger';
    }
}