<?php

namespace App\Filament\App\Resources\KpiDailyEntryResource\Widgets;

use App\Helpers\FilamentHelper;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Model;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class PerformanceSummaryOverview extends BaseWidget
{
    public ?Model $record = null;
    
    protected function getStats(): array
    {
        if (!$this->record || !$this->record->kpiEntryDetails) {
            return [];
        }

        $totalDetails = $this->record->kpiEntryDetails->count();
        
        if ($totalDetails === 0) {
            return [
                Stat::make('No KPI Details', 'No data available')
                    ->description('No KPI details found for this entry')
                    ->color('gray')
            ];
        }

        $onTargetDetails = $this->record->kpiEntryDetails->filter(function ($detail) {
            $percentage = FilamentHelper::calculatePerformancePercentage($detail);
            return $percentage >= 100;
        });

        $nearTargetDetails = $this->record->kpiEntryDetails->filter(function ($detail) {
            $percentage = FilamentHelper::calculatePerformancePercentage($detail);
            return $percentage >= 80 && $percentage < 100;
        });

        $belowTargetDetails = $this->record->kpiEntryDetails->filter(function ($detail) {
            $percentage = FilamentHelper::calculatePerformancePercentage($detail);
            return $percentage < 80;
        });

        $onTarget = $onTargetDetails->count();
        $nearTarget = $nearTargetDetails->count();
        $belowTarget = $belowTargetDetails->count();

        return [
            Stat::make('Total KPIs', $totalDetails)
                ->description('Overall performance summary')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('On Target', $onTarget)
                ->description($onTarget === 0 ? "No KPI Metric On Taarget" : $this->getMetricsDescription($onTargetDetails))
                ->color('success'),
                
            Stat::make('Near Target', $nearTarget)
                ->description($nearTarget === 0 ? "No KPI Metric Near Target" : $this->getMetricsDescription($nearTargetDetails))
                ->color('warning'),
                
            Stat::make('Below Target', $belowTarget)
                ->description( $belowTarget === 0 ? "No KPI Metric Below Target" : $this->getMetricsDescription($belowTargetDetails))
                ->color('danger'),
        ];
    }

    protected function getMetricsDescription($details): HtmlString
    {
        $metrics = $details->map(function ($detail) {
            return "{$detail->kpiMetric->name}: {$detail->value}";
        });

        return new HtmlString($metrics->implode('<br>'));
    }
}
