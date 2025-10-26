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

use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->spa()

            // Đổi primary để tránh “xanh nền trắng chữ” mặc định
            ->colors([
                'primary' => Color::hex('#00529C'), // xanh chữ chuẩn của sư phụ
            ])

            ->sidebarFullyCollapsibleOnDesktop()
            ->maxContentWidth('full')

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

            ->navigationGroups([
                NavigationGroup::make()->label('Đào tạo'),
                NavigationGroup::make()->label('Báo cáo'),
                NavigationGroup::make()->label('Thiết lập'),
                NavigationGroup::make()->label('User & Phân quyền'),
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
            ->authMiddleware([Authenticate::class]);
    }

    public function boot(): void
    {
        // Đổi handle để bust cache
        FilamentAsset::register([
            Js::make('nf-sidebar-js-20251026', resource_path('js/filament/admin/sidebar.js')),
            Css::make('nf-sidebar-css-20251026', resource_path('css/filament/admin/sidebar.css')),
        ], 'nf-lms-admin-20251026');

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            fn () => view('filament.components.sidebar-edge')
        );

        // Chèn 1 vị trí duy nhất
        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_END,
            fn () => view('filament.components.sidebar-mode-toggle')
        );
    }
}
