<?php

namespace App\Filament\Admin\Resources\TeamGoalsResource\Widgets;

use Carbon\Carbon;
use Filament\Support\RawJs;
use App\Models\KpiEntryDetail;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class HistoricalPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Historical Performance Chart';
    protected static ?string $maxHeight = '200px';
    public ?Model $record = null;

    public function getHistoricalPerformanceData(int $metricId, int $departmentId): array
    {
        $history = [];

        $unit = KpiEntryDetail::where('metric_id', $metricId)
            ->first()?->kpiMetric?->unit ?? '';

        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();

            // Ambil total value dalam bulan tersebut
            $monthlyTotal = KpiEntryDetail::whereHas('kpiDailyEntry', function (Builder $query) use ($departmentId, $monthStart, $monthEnd) {
                $query->where('status', 'approved');
                $query->whereHas('user', function (Builder $userQuery) use ($departmentId) {
                    $userQuery->whereHas('employee', function (Builder $employeeQuery) use ($departmentId) {
                        $employeeQuery->where('department_id', $departmentId);
                    });
                })
                    ->whereBetween('entry_date', [$monthStart, $monthEnd]);
            })
                ->where('metric_id', $metricId)
                ->avg('value') ?? 0;

            $history[$monthStart->format('M Y')] = $monthlyTotal;
        }

        return [
            'data' => $history,
            'unit' => $unit,
        ];
    }


    protected function getData(): array
    {
        $trendData = $this->getHistoricalPerformanceData($this->record->metric_id, $this->record->department_id);

        return [
            'datasets' => [
                [
                    'label' => 'Performance (Last 6 Months) in (' . $trendData['unit'] . ')',
                    'data' => array_values($trendData['data']),
                ],
            ],
            'labels' => array_keys($trendData['data']),
        ];
    }


    protected function getType(): string
    {
        return 'bar';
    }
}