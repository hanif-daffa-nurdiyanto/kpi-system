<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Widgets\AccountWidget;
use Filament\PanelProvider;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use App\Filament\App\Pages\Auth\Login;
use App\Filament\App\Widgets\EmployeeKpiOverviewTable;
use App\Filament\App\Widgets\EmployeePerformanceChart;
use App\Filament\App\Widgets\EmployeeStatsOverview;
use App\Filament\App\Widgets\EmployeeTeamStatOverview;
use App\Filament\App\Widgets\ManagerMonthlyTotalChart;
use App\Filament\App\Widgets\ManagerPerformanceChart;
use App\Filament\App\Widgets\ManagerStatsOverview;
use App\Filament\Manager\Widgets\ManagerKpiOverviewTable;
use Filament\Notifications\Notification;
use App\Filament\Widgets\DateRangeFilter;
use App\Filament\Widgets\StatDashboard;
use App\Http\Middleware\CheckPanelAccess;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Widgets\TeamPerformanceChart;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;

class AppPanelProvider extends PanelProvider
{

    public function panel(Panel $panel): Panel
    {
        Filament::serving(function () {
            if (session()->has('error')) {
                Notification::make()
                    ->title('Access Denied')
                    ->body(session('error'))
                    ->danger()
                    ->send();
            }
        });

        return $panel
            ->id('app')
            ->path('kpi')
            ->login()
            ->brandLogo(asset('assets/kpi_logo_light.png'))
            ->darkModeBrandLogo(asset('assets/kpi_logo_light.png'))
            ->brandLogoHeight('7rem')
            ->favicon(asset('assets/kpi_logo_square.png'))
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Zinc,
                'info' => Color::Blue,
                'primary' => Color::hex('#6D28D9'),
                'success' => Color::Green,
                'warning' => Color::Amber,
            ])
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            // ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
            ->widgets([
                EmployeeTeamStatOverview::class,
                EmployeeStatsOverview::class,
                EmployeePerformanceChart::class,
                ManagerPerformanceChart::class,
                EmployeeKpiOverviewTable::class,
                ManagerStatsOverview::class,
                ManagerMonthlyTotalChart::class,

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                CheckPanelAccess::class
            ])
            ->spa()
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->databaseNotifications()
            ->viteTheme('resources/css/filament/app/theme.css');
    }
}
