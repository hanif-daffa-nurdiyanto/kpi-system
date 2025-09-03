<?php

namespace App\Filament\App\Widgets;

use App\Models\KpiEntryDetail;
use App\Models\Employee;
use App\Models\Department;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManagerMonthlyTotalChart extends ChartWidget
{
    protected static ?string $heading = 'Team Monthly KPI Totals';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '400px';

    public ?string $filter = 'all';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole('manager');
    }

    protected function getFilters(): ?array
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('manager')) {
            return null;
        }

        $department = Department::where('manager_id', $user->id)->first();
        if (! $department) {
            return null;
        }

        $employees = Employee::where('department_id', $department->id)->with('user')->get();

        $filters = [];
        $filters['all'] = ' All Team Members';

        foreach ($employees as $employee) {
            if ($employee->user) {
                $filters[$employee->user_id] = ' ' . $employee->user->name;
            }
        }

        return $filters;
    }

    protected function getData(): array
    {
        try {
            $user = Auth::user();
            $department = Department::where('manager_id', $user->id)->first();
            if (! $department) return $this->emptyData();

            $employeeIds = $this->filter !== 'all'
                ? [$this->filter]
                : Employee::where('department_id', $department->id)->pluck('user_id')->toArray();

            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            $results = KpiEntryDetail::query()
                ->join('kpi_daily_entries', 'kpi_entry_details.entry_id', '=', 'kpi_daily_entries.id')
                ->join('kpi_metrics', 'kpi_entry_details.metric_id', '=', 'kpi_metrics.id')
                ->whereIn('kpi_daily_entries.user_id', $employeeIds)
                ->where('kpi_daily_entries.status', 'approved')
                ->whereBetween('kpi_daily_entries.submitted_at', [$start, $end])
                ->selectRaw('CONCAT(kpi_metrics.name, " (", kpi_metrics.unit, ")") as metric_label, SUM(kpi_entry_details.value) as total')
                ->groupBy('metric_label')
                ->get();

            if ($results->isEmpty()) {
                return $this->emptyData();
            }

            return [
                'labels' => $results->pluck('metric_label')->toArray(),
                'datasets' => [[
                    'label' => 'Monthly Totals',
                    'data' => $results->pluck('total')->map(fn ($val) => round($val, 2))->toArray(),
                    'backgroundColor' => $this->getChartColors(count($results)),
                    'borderWidth' => 0,
                    'hoverOffset' => 10,
                ]],
            ];
        } catch (\Exception $e) {
            Log::error('Manager Monthly Pie Error: ' . $e->getMessage());
            return $this->emptyData();
        }
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'align' => 'center',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 20,
                        'font' => [
                            'size' => 14,
                            'weight' => '500'
                        ],
                        'color' => '#374151',
                        'boxWidth' => 12,
                        'boxHeight' => 12,
                    ]
                ],
                'tooltip' => [
                    'enabled' => true,
                    'backgroundColor' => 'rgba(17, 24, 39, 0.95)',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#ffffff',
                    'borderColor' => 'rgba(255, 255, 255, 0.1)',
                    'borderWidth' => 1,
                    'cornerRadius' => 8,
                    'displayColors' => true,
                    'padding' => 16,
                    'titleFont' => [
                        'size' => 14,
                        'weight' => 'bold'
                    ],
                    'bodyFont' => [
                        'size' => 13
                    ],
                    'caretSize' => 8,
                    'caretPadding' => 8,
                ]
            ],
            'layout' => [
                'padding' => [
                    'left' => 20,
                    'right' => 20,
                    'top' => 20,
                    'bottom' => 20
                ]
            ],
            'animation' => [
                'animateRotate' => true,
                'animateScale' => true,
                'duration' => 1000,
                'easing' => 'easeOutQuart'
            ],
            'interaction' => [
                'intersect' => true,
                'mode' => 'nearest',
            ],
            'elements' => [
                'arc' => [
                    'borderWidth' => 2,
                    'spacing' => 2,
                ]
            ],

            'scales' => [
                'x' => [
                    'display' => false,
                    'grid' => [
                        'display' => false,
                        'drawBorder' => false,
                        'drawOnChartArea' => false,
                        'drawTicks' => false,
                    ],
                    'ticks' => [
                        'display' => false
                    ]
                ],
                'y' => [
                    'display' => false,
                    'grid' => [
                        'display' => false,
                        'drawBorder' => false,
                        'drawOnChartArea' => false,
                        'drawTicks' => false,
                    ],
                    'ticks' => [
                        'display' => false
                    ]
                ]
            ]

        ];
    }

    protected function getChartColors(int $count = 7): array
    {
        $colors = [
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
            '#14b8a6',
            '#f43f5e',
            '#6b7280',
            '#7c3aed',
            '#059669',
        ];
        return array_slice($colors, 0, max($count, 1));
    }

    protected function emptyData(): array
    {
        return [
            'labels' => ['No Data Available'],
            'datasets' => [[
                'label' => 'No Data',
                'data' => [1],
                'backgroundColor' => ['#f3f4f6'],
                'borderWidth' => 0,
            ]],
        ];
    }
}