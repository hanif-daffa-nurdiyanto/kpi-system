<?php

namespace App\Providers;

use App\Http\Responses\Auth\RegisterResponse;
use App\Models\KpiDailyEntry;
use App\Models\KpiEntryDetail;
use App\Observers\KpiDailyEntryObserver;
use App\Observers\KpiEntryDetailObserver;
use Illuminate\Support\ServiceProvider;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            RegistrationResponse::class,
            RegisterResponse::class
        );
    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        KpiEntryDetail::observe(KpiEntryDetailObserver::class);
        KpiDailyEntry::observe(KpiDailyEntryObserver::class);
    }
}
