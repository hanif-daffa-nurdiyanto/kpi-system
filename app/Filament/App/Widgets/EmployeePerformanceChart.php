<?php

namespace App\Filament\App\Widgets;

use App\Models\KpiEntryDetail;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeePerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'KPI Performance (Daily Average & Monthly Total)';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '350px';

    public ?string $filter = 'daily';

    public function getDescription(): ?string
    {
        return 'Show the daily performance or monthly total of your KPI Metric.';
    }

    protected function getFilters(): ?array
    {
        return [
            'daily' => '📊 Daily Average',
            'monthly' => '📈 Monthly Total',
        ];
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('employee');
    }

    protected function getData(): array
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->emptyData();
            }

            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            $query = KpiEntryDetail::query()
                ->join('kpi_daily_entries', 'kpi_entry_details.entry_id', '=', 'kpi_daily_entries.id')
                ->join('kpi_metrics', 'kpi_entry_details.metric_id', '=', 'kpi_metrics.id')
                ->where('kpi_daily_entries.user_id', $user->id)
                ->where('kpi_daily_entries.status', 'approved')
                ->whereBetween('kpi_daily_entries.submitted_at', [$start, $end]);

            if ($this->filter === 'monthly') {
                $results = $query
                    ->selectRaw('CONCAT(kpi_metrics.name, " (", kpi_metrics.unit, ")") as metric_label, SUM(kpi_entry_details.value) as total')
                    ->groupBy('metric_label')
                    ->get();

                return [
                    'labels' => $results->pluck('metric_label')->toArray(),
                    'datasets' => [[
                        'label' => 'Monthly Total',
                        'data' => $results->pluck('total')->map(fn ($val) => round($val, 2))->toArray(),
                        'backgroundColor' => $this->getChartColors(),
                        'borderRadius' => 6,
                    ]],
                ];
            }

            $entries = $query
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
                ];
                $i++;
            }

            return [
                'labels' => $days->toArray(),
                'datasets' => $datasets,
            ];
        } catch (\Exception $e) {
            Log::error('KPI Chart Error: ' . $e->getMessage());
            return $this->emptyData();
        }
    }

    protected function getType(): string
    {
        return $this->filter === 'monthly' ? 'bar' : 'line';
    }

    protected function getChartColors(): array
    {
        return [
            '#3b82f6', '#ef4444', '#8b5cf6', '#f59e0b', '#06b6d4', '#ec4899', '#10b981'
        ];
    }

    protected function hexToRgba(string $hex, float $alpha = 1): string
    {
        if (str_starts_with($hex, '#')) {
            $hex = substr($hex, 1);
        }

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
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
                'label' => 'No Data',
                'data' => [],
                'borderColor' => '#ccc',
                'backgroundColor' => 'transparent',
            ]],
        ];
    }
}
