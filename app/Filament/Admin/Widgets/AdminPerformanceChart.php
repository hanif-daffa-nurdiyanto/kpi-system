<?php

namespace App\Filament\Admin\Widgets;

use App\Models\KpiEntryDetail;
use App\Models\Department;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Department KPI Daily Performance';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '350px';

    public ?string $filter = 'all';

    public function getDescription(): ?string
    {
        $currentMonth = Carbon::now()->format('F Y');
        $filterText = $this->filter !== 'all' ? Department::find($this->filter)?->name ?? 'Selected Department' : 'All Departments';
        return "Displays daily KPI metrics performance from {$filterText} for {$currentMonth}.";
    }

    protected function getFilters(): ?array
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('super_admin')) return [];

        $filters = [
            'all' => 'All Departments',
        ];

        $departments = Department::orderBy('name')->get();

        foreach ($departments as $department) {
            $filters[$department->id] = $department->name;
        }

        return $filters;
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('super_admin');
    }

    protected function getData(): array
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->hasRole('super_admin')) {
                Log::info('Admin KPI Chart: User not super_admin');
                return $this->emptyData();
            }

            $activeFilter = $this->filter;

            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            $query = KpiEntryDetail::selectRaw('kpi_metrics.name as metric_name, kpi_metrics.unit as unit, DATE(kpi_daily_entries.submitted_at) as date, AVG(kpi_entry_details.value) as value')
                ->join('kpi_daily_entries', 'kpi_daily_entries.id', '=', 'kpi_entry_details.entry_id')
                ->join('employees', 'employees.user_id', '=', 'kpi_daily_entries.user_id')
                ->join('departments', 'departments.id', '=', 'employees.department_id')
                ->join('kpi_metrics', 'kpi_metrics.id', '=', 'kpi_entry_details.metric_id')
                ->where('kpi_daily_entries.status', 'approved')
                ->whereBetween('kpi_daily_entries.submitted_at', [$start, $end])
                ->orderBy('date')
                ->groupBy('kpi_metrics.name', 'kpi_metrics.unit', 'date');

            if ($activeFilter !== 'all') {
                $query->where('departments.id', $activeFilter);
            }

            $data = $query->get();

            Log::info('Admin KPI Chart: Query result count: ' . $data->count());

            $metrics = $data->groupBy('metric_name');
            $days = collect(range(1, $end->day))->map(fn($d) => Carbon::createFromDate($start->year, $start->month, $d)->format('d M'));
            $labels = $days->toArray();

            $datasets = [];
            $colors = $this->getChartColors();
            $colorIndex = 0;

            foreach ($metrics as $metricName => $records) {
                $unit = $records->first()->unit ?? "";

                $values = $days->map(function ($dayLabel) use ($records, $start) {
                    $date = Carbon::createFromFormat('d M', $dayLabel)
                        ->year($start->year)
                        ->format('Y-m-d');
                    $row = $records->firstWhere('date', $date);
                    return $row ? round($row->value, 2) : 0;
                });

                $datasets[] = [
                    'label' => "{$metricName} ({$unit})",
                    'data' => $values->toArray(),
                    'borderColor' => $colors[$colorIndex % count($colors)],
                    'backgroundColor' => $this->hexToRgba($colors[$colorIndex % count($colors)], 0.2),
                    'fill' => false,
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 6,
                ];
                $colorIndex++;
            }
            if (empty($datasets)) {
                $departmentName = $activeFilter !== 'all'
                    ? Department::find($activeFilter)?->name ?? 'Selected Department'
                    : 'All Departments';

                $datasets[] = [
                    'label' => "No Data - {$departmentName}",
                    'data' => array_fill(0, count($labels), 0),
                    'borderColor' => '#999',
                    'backgroundColor' => 'rgba(200, 200, 200, 0.2)',
                    'fill' => false,
                ];
            }
            Log::info('Admin KPI Chart: Total datasets created: ' . count($datasets));
            return [
                'datasets' => $datasets,
                'labels' => $labels,
            ];
        } catch (\Exception $e) {
            Log::error('Admin KPI Chart Error: ' . $e->getMessage());
            Log::error('Admin KPI Chart Stack: ' . $e->getTraceAsString());
            return $this->emptyData();
        }
    }
    protected function getType(): string
    {
        return 'line';
    }
    protected function getOptions(): array
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
                        'text' => 'Performance Score',
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
            '#3b82f6',
            '#ef4444',
            '#8b5cf6',
            '#f59e0b',
            '#06b6d4',
            '#ec4899',
            '#10b981',
            '#f97316',
            '#84cc16',
            '#6366f1',
            '#d946ef',
            '#06b6d4',
            '#f59e0b'
        ];
    }

    protected function hexToRgba(string $hex, float $alpha = 1): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = implode('', array_map(fn($c) => $c . $c, str_split($hex)));
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
