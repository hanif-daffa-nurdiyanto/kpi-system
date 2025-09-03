<?php

namespace App\Filament\App\Widgets;

use App\Models\KpiEntryDetail;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class EmployeeStatsOverview extends Widget
{
    protected static string $view = 'filament.app.widgets.employee-stats-cards';

    protected int | string | array $columnSpan = [
        'default' => 6,
        'sm' => 6,
        'md' => 6,
        'lg' => 6,
        'xl' => 6,
        '2xl' => 6,
    ];

    protected static ?int $sort = 1;

    public array $cards = [];

    public function mount(): void
    {
        $this->cards = $this->getTodayKpiCards();
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('employee');
    }

    public function getColumnSpan(): int | string | array
    {
        return [
            'default' => 6,
            'sm' => 6,
            'md' => 6,
            'lg' => 6,
            'xl' => 6,
            '2xl' => 6,
        ];
    }

    protected function getTodayKpiCards(): array
    {
        $user = Auth::user();
        $today = Carbon::today();

        if (!$user) {
            return [];
        }

        $entries = KpiEntryDetail::query()
            ->whereHas('kpiDailyEntry', function ($query) use ($today, $user) {
                $query->whereDate('entry_date', $today)
                      ->where('user_id', $user->id)
                      ->where('status', 'approved');
            })
            ->with('kpiMetric')
            ->get();

        $cards = [];

        foreach ($entries as $entry) {
            $metric = $entry->kpiMetric;

            if (!$metric || $metric->target_value == 0) {
                continue;
            }

            $actual = (float) $entry->value;
            $target = (float) $metric->target_value;
            $unit = $metric->unit;

            $progress = $target > 0 ? round(($actual / $target) * 100, 1) : 0;
            $progress = max(0, $progress);
            [$color, $status, $icon] = $this->getProgressStatus($progress, $actual, $target);

            $cards[] = [
                'name'     => $metric->name,
                'value'    => $actual,
                'target'   => $target,
                'unit'     => $unit,
                'status'   => $status,
                'progress' => $progress,
                'color'    => $color,
                'icon'     => $icon,
                'achievement_ratio' => $this->getAchievementRatio($actual, $target),
            ];
        }

        return $cards;
    }

    /**
     * Get progress status with more balanced thresholds
     */
    private function getProgressStatus(float $progress, float $actual, float $target): array
    {
        if ($progress >= 100) {
            $status = $progress >= 120 ? 'Excellent' : 'Target Achieved';
            $color = $progress >= 120 ? 'text-emerald-600' : 'text-green-600';
            $icon = $progress >= 120 ? 'heroicon-o-trophy' : 'heroicon-o-check-circle';
        } elseif ($progress >= 85) {
            $status = 'Near Target';
            $color = 'text-blue-600';
            $icon = 'heroicon-o-arrow-trending-up';
        } elseif ($progress >= 70) {
            $status = 'Making Progress';
            $color = 'text-amber-600';
            $icon = 'heroicon-o-clock';
        } elseif ($progress >= 50) {
            $status = 'Needs Attention';
            $color = 'text-orange-600';
            $icon = 'heroicon-o-exclamation-triangle';
        } else {
            $status = 'Critical';
            $color = 'text-red-600';
            $icon = 'heroicon-o-x-circle';
        }

        return [$color, $status, $icon];
    }

    /**
     * Get achievement ratio for better visualization
     */
    private function getAchievementRatio(float $actual, float $target): string
    {
        if ($target == 0) return '0:0';

        $ratio = $actual / $target;

        if ($ratio >= 1) {
            return '1:1+';
        } elseif ($ratio >= 0.85) {
            return '9:10';
        } elseif ($ratio >= 0.7) {
            return '7:10';
        } elseif ($ratio >= 0.5) {
            return '1:2';
        } else {
            return '<1:2';
        }
    }
}