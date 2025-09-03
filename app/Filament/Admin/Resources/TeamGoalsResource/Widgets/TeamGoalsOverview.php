<?php

namespace App\Filament\Admin\Resources\TeamGoalsResource\Widgets;

use App\Models\TeamGoals;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class TeamGoalsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();

        $totalGoals = TeamGoals::count();
        $totalGoalsChart = collect(range(0, 11))->map(function ($weeksAgo) {
            $startOfWeek = Carbon::now()->subWeeks($weeksAgo)->startOfWeek();
            $endOfWeek = Carbon::now()->subWeeks($weeksAgo)->endOfWeek();

            return TeamGoals::whereBetween('created_at', [
                $startOfWeek,
                $endOfWeek
            ])->count();
        })->toArray();

        $activeGoals = TeamGoals::where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->count();
        $activeGoalsChart = collect(range(0, 11))->map(function ($weeksAgo) {
            $targetDate = Carbon::now()->subWeeks($weeksAgo);

            return TeamGoals::where('is_active', true)
                ->where('start_date', '<=', $targetDate)
                ->where('end_date', '>=', $targetDate)
                ->count();
        })->toArray();

        $upcomingGoals = TeamGoals::where('is_active', true)
            ->where('start_date', '>', $now)
            ->count();
        $upcomingGoalsChart = collect(range(0, 11))->map(function ($weeksAgo) {
            $targetDate = Carbon::now()->subWeeks($weeksAgo);

            return TeamGoals::where('is_active', true)
                ->where('start_date', '>', $targetDate)
                ->count();
        })->toArray();

        $completedGoals = TeamGoals::where('end_date', '<', $now)
            ->count();
        $completedGoalsChart = collect(range(0, 11))->map(function ($weeksAgo) {
            $targetDate = Carbon::now()->subWeeks($weeksAgo);

            return TeamGoals::where('end_date', '<', $targetDate)
                ->count();
        })->toArray();

        return [
            Stat::make('Total Goals', $totalGoals)
                ->color('gray')
                ->icon('heroicon-o-clipboard-document-list')
                ->chart($totalGoalsChart),

            Stat::make('Active Goal', $activeGoals)
                ->color('success')
                ->icon('heroicon-o-play')
                ->chart($activeGoalsChart),

            Stat::make('Goal Completed', $completedGoals)
                ->color('primary')
                ->icon('heroicon-o-check-badge')
                ->chart($completedGoalsChart),
        ];
    }
}
