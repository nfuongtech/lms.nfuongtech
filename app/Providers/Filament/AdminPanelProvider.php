<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
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
//use App\Filament\Widgets\ThongKeHocVienChart;


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
            // Tắt discoverWidgets nếu muốn chỉ định thủ công
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                 ThongKeHocVienWidget::class,
//                 ThongKeHocVienChart::class,
                 ChiPhiDaoTaoChart::class,
//                 Widgets\AccountWidget::class,
//                 Widgets\FilamentInfoWidget::class,
            ])
            ->renderHook(
                'panels::sidebar.nav.after',
                fn () => view('filament.components.custom-navigation'),
            )
            ->renderHook(
                'panels::sidebar.footer',
                fn () => view('filament.components.sidebar-controls'),
            )
            ->middleware([
                EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class,
                AuthenticateSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class,
                SubstituteBindings::class, DisableBladeIconComponents::class, DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class])
            ->spa()
            ->maxContentWidth('full')
            ->navigationGroups([
                 NavigationGroup::make()->label('Đào tạo'), NavigationGroup::make()->label('Báo cáo'),
                 NavigationGroup::make()->label('Thiết lập')->items([ NavigationGroup::make()->label('User & Phân quyền')->items([]), ]),
             ]);
    }
}
