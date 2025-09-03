<?php

namespace App\Filament\App\Widgets;

use App\Models\KpiEntryDetail;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;

class EmployeeKpiOverviewTable extends BaseWidget
{
    protected static ?string $heading = 'Your Weekly KPI Performance Summary';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 5;

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-clipboard-document-check';
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No KPI entries found for this week';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'You have not submitted any KPI entries for the current week. KPI entries will appear here once submitted.';
    }

    protected function getTablePagination(): ?array
    {
        return [
            'default' => 5,
        ];
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('employee');
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        $userId = Auth::id();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        return KpiEntryDetail::query()
            ->whereHas('kpiDailyEntry', function (Builder $query) use ($userId, $startOfWeek, $endOfWeek) {
                $query->where('user_id', $userId)
                    ->whereBetween('entry_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')]);
            })
            ->with(['kpiMetric', 'kpiDailyEntry.employee.department'])
            ->orderBy('created_at', 'desc');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('created_at')
                ->label('Entry Time')
                ->dateTime('M d, Y h:i A')
                ->sortable()
                ->color('gray'),

            TextColumn::make('kpiDailyEntry.entry_date')
                ->label('KPI Date')
                ->date('l, M d')
                ->sortable()
                ->color('gray'),

            TextColumn::make('kpiMetric.name')
                ->label('KPI Metric')
                ->searchable()
                ->sortable()
                // ->description(fn ($record) => $record->kpiMetric->description ?? '')
                ->icon('heroicon-o-chart-bar')
                ->weight('bold'),

            TextColumn::make('kpiMetric.target_value')
                ->label('Target')
                ->formatStateUsing(fn ($state, $record) => number_format($state, 0) . ' ' . $record->kpiMetric->unit)
                ->icon('heroicon-o-flag')
                ->color('gray'),

            TextColumn::make('value')
                ->label('Your Achievement')
                ->formatStateUsing(fn ($state, $record) => number_format($state, 0) . ' ' . $record->kpiMetric->unit)
                ->icon('heroicon-o-user')
                ->color('primary')
                ->weight('bold'),

            TextColumn::make('team_total')
                ->label('Team Total')
                ->getStateUsing(function ($record) {
                    $departmentId = optional(optional($record->kpiDailyEntry->employee)->department)->id;

                    if (! $departmentId) {
                        return '-';
                    }

                    $metricId = $record->metric_id;
                    $entryDate = $record->kpiDailyEntry->entry_date;

                    $total = KpiEntryDetail::where('metric_id', $metricId)
                        ->whereHas('kpiDailyEntry.employee', function ($query) use ($departmentId) {
                            $query->where('department_id', $departmentId);
                        })
                        ->whereHas('kpiDailyEntry', function ($query) use ($entryDate) {
                            $query->whereDate('entry_date', $entryDate);
                        })
                        ->sum('value');

                    return number_format($total, 0) . ' ' . $record->kpiMetric->unit;
                })
                ->icon('heroicon-o-users')
                ->color('success'),

            TextColumn::make('achievement_progress')
                ->label('Progress')
                ->getStateUsing(function ($record) {
                    if (! $record->kpiMetric || $record->kpiMetric->target_value == 0) {
                        return '-';
                    }

                    $percentage = ($record->value / $record->kpiMetric->target_value) * 100;
                    return number_format($percentage, 1) . '%';
                })
                ->badge()
                ->color(function ($record) {
                    if (! $record->kpiMetric || $record->kpiMetric->target_value == 0) {
                        return 'gray';
                    }

                    $percentage = ($record->value / $record->kpiMetric->target_value) * 100;

                    return match (true) {
                        $percentage >= 100 => 'success',
                        $percentage >= 80 => 'primary',
                        $percentage >= 50 => 'warning',
                        default => 'danger',
                    };
                }),

            TextColumn::make('status')
                ->label('Status')
                ->html()
                ->getStateUsing(function ($record) {
                    if (
                        ! $record->kpiMetric ||
                        $record->kpiMetric->target_value == 0 ||
                        $record->value === null
                    ) {
                        return 'needs-attention';
                    }

                    $percentage = ($record->value / $record->kpiMetric->target_value) * 100;

                    return [
                        'percentage' => $percentage,
                        'status' => match (true) {
                            $percentage >= 100 => 'above-target',
                            $percentage >= 80 => 'on-track',
                            $percentage >= 50 => 'needs-improvement',
                            default => 'below-target',
                        }
                    ];
                })
                ->view('filament.tables.columns.status-indicator'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('metric_id')
                ->label('KPI Metric')
                ->relationship('kpiMetric', 'name')
                ->multiple()
                ->preload(),

            Tables\Filters\Filter::make('week_selector')
                ->form([
                    DatePicker::make('week')
                        ->label('Select Week')
                        ->default(now()),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $date = $data['week'] ? Carbon::parse($data['week']) : Carbon::now();
                    $startOfWeek = $date->copy()->startOfWeek();
                    $endOfWeek = $date->copy()->endOfWeek();

                    return $query->whereHas(
                        'kpiDailyEntry',
                        fn (Builder $query) => $query->whereBetween(
                            'entry_date',
                            [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')]
                        )
                    );
                })
                ->indicateUsing(function (array $data): ?string {
                    if (! $data['week']) {
                        return null;
                    }

                    $date = Carbon::parse($data['week']);
                    $startOfWeek = $date->copy()->startOfWeek()->format('M d');
                    $endOfWeek = $date->copy()->endOfWeek()->format('M d, Y');

                    return "Week of {$startOfWeek} - {$endOfWeek}";
                }),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        $today = Carbon::today()->format('Y-m-d');

        return [
            Tables\Actions\Action::make('add_entry')
                ->label('Add Today\'s Entry')
                ->icon('heroicon-o-plus-circle')
                ->url(route('filament.app.resources.kpi-daily-entries.create', ['date' => $today]))
                ->color('primary')
                ->button(),

            Tables\Actions\Action::make('current_week_info')
                ->label(function () {
                    $startOfWeek = Carbon::now()->startOfWeek()->format('M d');
                    $endOfWeek = Carbon::now()->endOfWeek()->format('M d, Y');
                    return "Current Week: {$startOfWeek} - {$endOfWeek}";
                })
                ->icon('heroicon-o-calendar')
                ->color('gray')
                ->disabled(),
        ];
    }
}