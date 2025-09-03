<?php

namespace App\Filament\Manager\Widgets;

use App\Models\KpiEntryDetail;
use App\Models\Employee;
use App\Models\Department;
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

class ManagerKpiOverviewTable extends BaseWidget
{
    protected static ?string $heading = 'Team Weekly KPI Performance Summary';
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
        return 'Your team has not submitted any KPI entries for the current week. KPI entries will appear here once submitted.';
    }

    protected function getTablePagination(): ?array
    {
        return [
            'default' => 10,
        ];
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('manager');
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('manager')) {
            return KpiEntryDetail::query()->whereRaw('1 = 0');
        }

        $department = Department::where('manager_id', $user->id)->first();

        if (!$department) {
            return KpiEntryDetail::query()->whereRaw('1 = 0');
        }

        $employeeIds = Employee::where('department_id', $department->id)
            ->pluck('user_id')
            ->toArray();

        if (empty($employeeIds)) {
            return KpiEntryDetail::query()->whereRaw('1 = 0');
        }

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        return KpiEntryDetail::query()
            ->join('kpi_daily_entries', 'kpi_entry_details.entry_id', '=', 'kpi_daily_entries.id')
            ->join('kpi_metrics', 'kpi_entry_details.metric_id', '=', 'kpi_metrics.id')
            ->join('users', 'kpi_daily_entries.user_id', '=', 'users.id')
            ->whereIn('kpi_daily_entries.user_id', $employeeIds)
            ->whereBetween('kpi_daily_entries.entry_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->select([
                'kpi_entry_details.id',
                'kpi_entry_details.entry_id',
                'kpi_entry_details.metric_id',
                'kpi_entry_details.value',
                'kpi_entry_details.created_at',
                'kpi_daily_entries.entry_date',
                'kpi_daily_entries.user_id',
                'kpi_metrics.name as metric_name',
                'kpi_metrics.unit as metric_unit',
                'kpi_metrics.target_value',
                'kpi_metrics.description as metric_description',
                'users.name as employee_name'
            ])
            ->orderBy('kpi_entry_details.created_at', 'desc');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('employee_name')
                ->label('Employee')
                ->searchable()
                ->sortable()
                ->icon('heroicon-o-user')
                ->weight('bold')
                ->color('primary'),

            TextColumn::make('created_at')
                ->label('Entry Time')
                ->dateTime('M d, Y h:i A')
                ->sortable()
                ->color('gray'),

            TextColumn::make('entry_date')
                ->label('KPI Date')
                ->date('l, M d')
                ->sortable()
                ->color('gray'),

            TextColumn::make('metric_name')
                ->label('KPI Metric')
                ->searchable()
                ->sortable()
                // ->description(fn ($record) => $record->metric_description ?? '')
                ->icon('heroicon-o-chart-bar')
                ->weight('bold'),

            TextColumn::make('target_value')
                ->label('Target')
                ->formatStateUsing(fn ($state, $record) => number_format($state, 0) . ' ' . $record->metric_unit)
                ->icon('heroicon-o-flag')
                ->color('gray'),

            TextColumn::make('value')
                ->label('Achievement')
                ->formatStateUsing(fn ($state, $record) => number_format($state, 0) . ' ' . $record->metric_unit)
                ->icon('heroicon-o-trophy')
                ->color('primary')
                ->weight('bold'),

            TextColumn::make('team_total')
                ->label('Team Total')
                ->getStateUsing(function ($record) {
                    $user = Auth::user();
                    $department = Department::where('manager_id', $user->id)->first();

                    if (!$department) {
                        return '-';
                    }

                    $employeeIds = Employee::where('department_id', $department->id)
                        ->pluck('user_id')
                        ->toArray();

                    $total = KpiEntryDetail::join('kpi_daily_entries', 'kpi_entry_details.entry_id', '=', 'kpi_daily_entries.id')
                        ->where('kpi_entry_details.metric_id', $record->metric_id)
                        ->whereIn('kpi_daily_entries.user_id', $employeeIds)
                        ->whereDate('kpi_daily_entries.entry_date', $record->entry_date)
                        ->sum('kpi_entry_details.value');

                    return number_format($total, 0) . ' ' . $record->metric_unit;
                })
                ->icon('heroicon-o-users')
                ->color('success'),

            TextColumn::make('achievement_progress')
                ->label('Progress')
                ->getStateUsing(function ($record) {
                    if (!$record->target_value || $record->target_value == 0) {
                        return '-';
                    }

                    $percentage = ($record->value / $record->target_value) * 100;
                    return number_format($percentage, 1) . '%';
                })
                ->badge()
                ->color(function ($record) {
                    if (!$record->target_value || $record->target_value == 0) {
                        return 'gray';
                    }

                    $percentage = ($record->value / $record->target_value) * 100;

                    return match (true) {
                        $percentage >= 100 => 'success',
                        $percentage >= 80 => 'primary',
                        $percentage >= 50 => 'warning',
                        default => 'danger',
                    };
                }),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->getStateUsing(function ($record) {
                    if (!$record->target_value || $record->target_value == 0 || $record->value === null) {
                        return 'Needs Attention';
                    }

                    $percentage = ($record->value / $record->target_value) * 100;

                    return match (true) {
                        $percentage >= 100 => 'Above Target',
                        $percentage >= 80 => 'On Track',
                        $percentage >= 50 => 'Needs Improvement',
                        default => 'Below Target',
                    };
                })
                ->color(function ($record) {
                    if (!$record->target_value || $record->target_value == 0 || $record->value === null) {
                        return 'gray';
                    }

                    $percentage = ($record->value / $record->target_value) * 100;

                    return match (true) {
                        $percentage >= 100 => 'success',
                        $percentage >= 80 => 'primary',
                        $percentage >= 50 => 'warning',
                        default => 'danger',
                    };
                }),
        ];
    }

    protected function getTableFilters(): array
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('manager')) {
            return [];
        }

        $department = Department::where('manager_id', $user->id)->first();

        if (!$department) {
            return [];
        }

        $employees = Employee::where('department_id', $department->id)
            ->with('user')
            ->get();

        return [
            Tables\Filters\SelectFilter::make('user_id')
                ->label('Employee')
                ->options(function () use ($employees) {
                    $options = [];
                    foreach ($employees as $employee) {
                        if ($employee->user) {
                            $options[$employee->user_id] = $employee->user->name;
                        }
                    }
                    return $options;
                })
                ->query(function (Builder $query, array $data): Builder {
                    if (isset($data['value']) && $data['value']) {
                        return $query->where('kpi_daily_entries.user_id', $data['value']);
                    }
                    return $query;
                })
                ->placeholder('All Employees')
                ->multiple(false),

            Tables\Filters\SelectFilter::make('metric_id')
                ->label('KPI Metric')
                ->options(function () {
                    return \App\Models\KpiMetric::pluck('name', 'id')->toArray();
                })
                ->query(function (Builder $query, array $data): Builder {
                    if (isset($data['value']) && $data['value']) {
                        return $query->where('kpi_entry_details.metric_id', $data['value']);
                    }
                    return $query;
                })
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

                    return $query->whereBetween(
                        'kpi_daily_entries.entry_date',
                        [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')]
                    );
                })
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['week']) {
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
        return [
            Tables\Actions\Action::make('team_summary')
                ->label('Team Summary')
                ->icon('heroicon-o-chart-bar-square')
                ->color('primary')
                ->action(function () {
                    // You can add modal or redirect to team summary page
                })
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

    protected function getTableRecordAction(): ?string
    {
        return null; // Disable row click action
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view_details')
                ->label('View Details')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->action(function ($record) {
                    // Add your view details logic here
                }),
        ];
    }
}