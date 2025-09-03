<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Widgets\AdminPerformanceChart;
use App\Filament\Admin\Widgets\AdminStatsOverview;
use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Admin\Widgets\ChartLine;
use App\Filament\Widgets\DateRangeFilter;
use App\Http\Middleware\CheckPanelAccess;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Admin\Widgets\StatDashboard;
use App\Filament\Widgets\TeamPerformanceChart;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use App\Filament\Admin\Widgets\DepartmentSelector;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\Filament\Widgets\StatDashboard as WidgetsStatDashboard;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo(asset('assets/kpi_logo_light.png'))
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
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                // DepartmentSelector::class,
                // DateRangeFilter::class,
                // WidgetsStatDashboard::class,
                AdminStatsOverview::class,
                AdminPerformanceChart::class,
                // TeamPerformanceChart::class
                // ChartLine::class,
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
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
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->databaseNotifications()
            ->spa()
            ->viteTheme('resources/css/filament/app/theme.css');
    }
}
