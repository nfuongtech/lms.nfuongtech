<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use App\Filament\Widgets\ChiPhiDaoTaoChart;
use App\Filament\Widgets\ThongKeHocVienWidget;
use App\Models\AdminNavigationSetting;
use App\Services\AdminNavigationBuilder;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors(['primary' => Color::Amber])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([Pages\Dashboard::class])
            ->widgets([
                ThongKeHocVienWidget::class,
                ChiPhiDaoTaoChart::class,
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
            ])
            ->authMiddleware([Authenticate::class])
            ->spa()
            ->maxContentWidth('full')
            ->navigation(fn (NavigationBuilder $navigation): NavigationBuilder => AdminNavigationBuilder::build($navigation))
            ->renderHook('panels::sidebar.nav.start', fn (): string => view('filament.hooks.sidebar-preferences')->render())
            ->renderHook('panels::head.end', fn (): string => view('filament.hooks.sidebar-preferences-styles')->render())
            ->renderHook('panels::body.end', function (): string {
                $mode = AdminNavigationSetting::instance()->sidebar_mode;

                return view('filament.hooks.sidebar-preferences-script', [
                    'sidebarMode' => $mode,
                ])->render();
            });
    }
}
