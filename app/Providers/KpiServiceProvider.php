<?php

namespace App\Providers;

use App\Services\KpiStatsService;
use Illuminate\Support\ServiceProvider;

class KpiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(KpiStatsService::class, function () {
            return new KpiStatsService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
