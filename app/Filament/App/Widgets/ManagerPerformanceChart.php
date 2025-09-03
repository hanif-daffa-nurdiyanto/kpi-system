<?php

namespace App\Filament\App\Widgets;

use App\Models\KpiEntryDetail;
use App\Models\Employee;
use App\Models\Department;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManagerPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Team KPI Daily Performance';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '350px';

    public ?string $filter = 'all';

    public function getDescription(): ?string
    {
        return 'Displays daily KPI performance of your team or selected employee.';
    }

    protected function getFilters(): ?array
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('manager')) return [];

        $department = Department::where('manager_id', $user->id)->first();
        if (!$department) return [];

        $filters = [
            'all' => 'All Team Members',
        ];

        $employees = Employee::where('department_id', $department->id)
            ->with('user')
            ->get();

        foreach ($employees as $employee) {
            if ($employee->user) {
                $filters['employee_' . $employee->user_id] = $employee->user->name;
            }
        }

        return $filters;
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('manager');
    }

    protected function getData(): array
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->hasRole('manager')) {
                return $this->emptyData();
            }

            $department = Department::where('manager_id', $user->id)->first();
            if (!$department) {
                return $this->emptyData();
            }

            $selectedEmployeeId = null;
            $chartLabel = 'Team';

            if ($this->filter !== 'all') {
                if (str_starts_with($this->filter, 'employee_')) {
                    $selectedEmployeeId = str_replace('employee_', '', $this->filter);
                    $selectedEmployee = Employee::where('department_id', $department->id)
                        ->where('user_id', $selectedEmployeeId)
                        ->with('user')
                        ->first();

                    if (!$selectedEmployee || !$selectedEmployee->user) {
                        return $this->emptyData();
                    }

                    $chartLabel = $selectedEmployee->user->name;
                } else {
                    return $this->emptyData();
                }
            }

            $employeeIds = $selectedEmployeeId
                ? [$selectedEmployeeId]
                : Employee::where('department_id', $department->id)->pluck('user_id')->toArray();

            if (empty($employeeIds)) {
                return $this->emptyData();
            }

            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            $entries = KpiEntryDetail::query()
                ->join('kpi_daily_entries', 'kpi_entry_details.entry_id', '=', 'kpi_daily_entries.id')
                ->join('kpi_metrics', 'kpi_entry_details.metric_id', '=', 'kpi_metrics.id')
                ->whereIn('kpi_daily_entries.user_id', $employeeIds)
                ->where('kpi_daily_entries.status', 'approved')
                ->whereBetween('kpi_daily_entries.submitted_at', [$start, $end])
                ->selectRaw('DATE(kpi_daily_entries.submitted_at) as date, CONCAT(kpi_metrics.name, " (", kpi_metrics.unit, ")") as metric_label, AVG(kpi_entry_details.value / NULLIF(kpi_metrics.target_value, 0) * 100) as progress')
                ->groupBy('date', 'metric_label')
                ->orderBy('date')
                ->get()
                ->groupBy('metric_label');

            $days = collect(range(1, $end->day))->map(fn ($d) => Carbon::createFromDate($start->year, $start->month, $d)->format('d M'));

            $datasets = [];
            $colors = $this->getChartColors();
            $i = 0;

            foreach ($entries as $metricLabel => $data) {
                $values = $days->map(function ($dayLabel) use ($data) {
                    $date = Carbon::createFromFormat('d M', $dayLabel)->format('Y-m-d');
                    $row = $data->firstWhere('date', $date);
                    return $row ? round($row->progress, 2) : 0;
                });

                $datasets[] = [
                    'type' => 'line',
                    'label' => $metricLabel,
                    'data' => $values,
                    'borderColor' => $colors[$i % count($colors)],
                    'backgroundColor' => $this->hexToRgba($colors[$i % count($colors)], 0.2),
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 6,
                ];
                $i++;
            }

            return [
                'labels' => $days->toArray(),
                'datasets' => $datasets,
            ];
        } catch (\Exception $e) {
            Log::error('Manager KPI Chart Error: ' . $e->getMessage());
            return $this->emptyData();
        }
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getChartOptions(): array
    {
        return [
            'animation' => [
                'duration' => 1000,
                'easing' => 'easeOutQuart',
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Progress (%)',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Date',
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }

    protected function getChartColors(): array
    {
        return [
            '#3b82f6', '#ef4444', '#8b5cf6', '#f59e0b', '#06b6d4', '#ec4899', '#10b981'
        ];
    }

    protected function hexToRgba(string $hex, float $alpha = 1): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = implode('', array_map(fn ($c) => $c . $c, str_split($hex)));
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba($r, $g, $b, $alpha)";
    }

    protected function emptyData(): array
    {
        return [
            'labels' => [],
            'datasets' => [[
                'label' => 'No Data Available',
                'data' => [],
                'borderColor' => '#ccc',
                'backgroundColor' => 'transparent',
            ]],
        ];
    }
}
