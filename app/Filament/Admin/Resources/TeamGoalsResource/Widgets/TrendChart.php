<?php

namespace App\Filament\Admin\Resources\TeamGoalsResource\Widgets;

use Carbon\Carbon;
use App\Models\KpiEntryDetail;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TrendChart extends ChartWidget
{
    protected static ?string $heading = 'Last 14 Days Trend Data Chart';
    protected static ?string $maxHeight = '200px';

    public ?Model $record = null;

    protected function getData(): array
    {
        $trendData = [];

        for ($i = 13; $i >= 0; $i--) {
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
                    'label' => 'Last 14 Days Trend Data in (' . $unit . ')',
                    'data' => $dataValues,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}