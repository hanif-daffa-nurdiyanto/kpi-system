<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Department;
use App\Models\KpiEntryDetail;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TeamPerformanceChart extends ChartWidget
{
    protected string|int|array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';
    public ?string $filter = 'all';
    protected static ?int $sort = 3;

    public function getHeading(): string
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            return 'All Department Performance Chart';
        } elseif ($user->hasRole('manager')) {
            $departmentName = $user->employee->department->name ?? 'manager';
            return "Performance Chart - $departmentName";
        } else {
            $username = $user->name;
            return "$username Performance Chart";
        }
    }

    protected function getFilters(): array
    {
        if (Auth::user()->hasRole('super_admin')) {
            $departments = Department::orderBy('name')->pluck('name', 'id')->toArray();
            return ['all' => 'All Departments'] + $departments;
        }

        return [];
    }


    protected function getSuperAdminChart()
    {
        $activeFilter = $this->filter;

        $query = KpiEntryDetail::selectRaw('departments.name as department_name,kpi_metrics.unit as unit, DATE(kpi_daily_entries.submitted_at) as date, AVG(kpi_entry_details.value) as value')
            ->join('kpi_daily_entries', 'kpi_daily_entries.id', '=', 'kpi_entry_details.entry_id')
            ->join('employees', 'employees.user_id', '=', 'kpi_daily_entries.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->join('kpi_metrics', 'kpi_metrics.id', '=', 'kpi_entry_details.metric_id')
            ->where('kpi_daily_entries.status', 'approved')
            ->where('kpi_daily_entries.submitted_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'))
            ->orderBy('date')
            ->groupBy('departments.name', 'date', 'unit');

        if ($activeFilter !== 'all') {
            $query->where('departments.id', $activeFilter);
        }

        $data = $query->get();

        $departments = $data->groupBy('department_name');

        $labels = collect(range(0, 29))->map(fn($i) => now()->subDays($i)->toDateString())->sort()->values()->toArray();

        $datasets = [];

        foreach ($departments as $department => $records) {
            $unit = $records->first()->unit ?? "";
            $datasets[] = [
                'label' => "Performance {$department} in ({$unit})",
                'data' => collect($labels)
                    ->map(fn($date) => $records->firstWhere('date', $date)->value ?? 0)
                    ->toArray(),
                'borderColor' => '#' . substr(md5($department), 0, 6),
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'fill' => false,
            ];
        }

        if (empty($datasets)) {
            $departmentName = $activeFilter !== 'all'
                ? Department::find($activeFilter)?->name ?? 'No Data'
                : 'No Data';

            $datasets[] = [
                'label' => $departmentName,
                'data' => array_fill(0, count($labels), 0),
                'borderColor' => '#999',
                'backgroundColor' => 'rgba(200, 200, 200, 0.2)',
                'fill' => false,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getManagerChart(): array
    {
        $user = Auth::user();
        $departmentId = $user->department->id ?? null;

        $query = KpiEntryDetail::selectRaw('kpi_metrics.unit as unit, DATE(kpi_daily_entries.submitted_at) as date, AVG(kpi_entry_details.value) as value')
            ->join('kpi_daily_entries', 'kpi_daily_entries.id', '=', 'kpi_entry_details.entry_id')
            ->join('employees', 'employees.user_id', '=', 'kpi_daily_entries.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->join('kpi_metrics', 'kpi_metrics.id', '=', 'kpi_entry_details.metric_id')
            ->where('kpi_daily_entries.submitted_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'))
            ->where('departments.id', $departmentId)
            ->where('kpi_daily_entries.status', 'approved')
            ->orderBy('date')
            ->groupBy('date', 'unit')
            ->get();

        $unit = $query->first()->unit ?? "";
        $labels = collect(range(0, 29))->map(fn($i) => now()->subDays($i)->toDateString())->sort()->values()->toArray();
        $datasets = [
            [
                'label' => (Department::find($departmentId)->name ?? 'Department') . " - Performance in the last 1 month in ({$unit})",
                'data' => collect($labels)->map(fn($date) => $query->firstWhere('date', $date)->value ?? 0)->toArray(),
                'borderColor' => '#FF6384',
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'fill' => false,
            ]
        ];

        return ['datasets' => $datasets, 'labels' => $labels];
    }

    protected function getUserChart(): array
    {
        $user = Auth::user();

        $query = KpiEntryDetail::selectRaw('kpi_metrics.unit as unit, DATE(kpi_daily_entries.submitted_at) as date, AVG(kpi_entry_details.value) as value')
            ->join('kpi_daily_entries', 'kpi_daily_entries.id', '=', 'kpi_entry_details.entry_id')
            ->join('kpi_metrics', 'kpi_metrics.id', '=', 'kpi_entry_details.metric_id')
            ->where('kpi_daily_entries.user_id', $user->id)
            ->where('kpi_daily_entries.status', 'approved')
            ->where('kpi_daily_entries.submitted_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'))
            ->orderBy('date')
            ->groupBy('date', 'unit')
            ->get();

        $unit = $query->first()->unit ?? "";
        $labels = collect(range(0, 29))->map(fn($i) => now()->subDays($i)->toDateString())->sort()->values()->toArray();
        $datasets = [
            [
                'label' => "Your Performance in the last 1 month in ({$unit})",
                'data' => collect($labels)->map(fn($date) => $query->firstWhere('date', $date)->value ?? 0)->toArray(),
                'borderColor' => '#36A2EB',
                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                'fill' => false,
            ]
        ];

        return ['datasets' => $datasets, 'labels' => $labels];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Performance Score',
                    ]
                ]
            ]
        ];
    }

    protected function getData(): array
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            return $this->getSuperAdminChart();
        } else if ($user->hasRole('manager')) {
            return $this->getManagerChart();
        } else {
            return $this->getUserChart();
        }
    }

    protected function getType(): string
    {
        return 'line';
    }
}
