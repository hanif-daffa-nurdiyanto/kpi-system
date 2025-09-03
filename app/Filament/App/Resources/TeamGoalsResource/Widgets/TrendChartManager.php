<?php

namespace App\Filament\App\Resources\TeamGoalsResource\Widgets;

use App\Models\KpiEntryDetail;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class TrendChartManager extends ChartWidget
{
    protected static ?string $heading = 'Last 7 Days Trend Data Chart';
    protected static ?string $maxHeight = '200px';

    public ?Model $record = null;

    protected function getData(): array
    {
        $trendData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();

            $unit = KpiEntryDetail::where('metric_id', $this->record->metric_id)
                ->first()?->kpiMetric?->unit ?? '';

            $totalValue = KpiEntryDetail::where('metric_id', $this->record->metric_id)
                ->whereDate('created_at', $date)
                ->whereHas('kpiDailyEntry', function ($query) {
                    $query->where('status', 'approved');
                })
                ->avg('value') ?? 0;
            ;

            $trendData[$date] = $totalValue;
        }

        $labels = collect($trendData)->keys()->map(fn($date) => Carbon::parse($date)->format('d M'));
        $dataValues = collect($trendData)->values();

        return [
            'datasets' => [
                [
                    'label' => 'Last 7 Days Trend Data in (' . $unit . ')',
                    'data' => $dataValues,
                    'x' => [
                        'offset' => true,
                    ]
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'offset' => true,
                ],
            ],
        ];
    }
}
