<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
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
use App\Models\AdminNavigationItem;
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
            ->viteTheme('resources/css/filament/admin/theme.css')
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
            ->middleware([
                EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class,
                AuthenticateSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class,
                SubstituteBindings::class, DisableBladeIconComponents::class, DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class])
            ->spa()
            ->maxContentWidth('full')
            ->renderHook('panels::topbar.start', fn () => view('filament.admin.sidebar-mode-switcher'))
            ->navigationGroups(fn () => array_merge(
                $this->getDefaultNavigationGroups(),
                $this->getCustomNavigationGroups(),
            ));
    }

    protected function getDefaultNavigationGroups(): array
    {
        return [
            NavigationGroup::make()->label('Đào tạo'),
            NavigationGroup::make()->label('Báo cáo'),
            NavigationGroup::make()->label('Thiết lập'),
        ];
    }

    protected function getCustomNavigationGroups(): array
    {
        $items = AdminNavigationItem::query()
            ->root()
            ->active()
            ->with(['children' => fn ($query) => $query->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (AdminNavigationItem $item) => $this->buildNavigationItem($item))
            ->filter()
            ->values()
            ->all();

        if (empty($items)) {
            return [];
        }

        return [
            NavigationGroup::make()
                ->label('Menu tùy biến')
                ->items($items),
        ];
    }

    protected function buildNavigationItem(AdminNavigationItem $item): ?NavigationItem
    {
        if (! $item->is_active) {
            return null;
        }

        $navigationItem = NavigationItem::make($item->label)
            ->icon($item->icon ?: 'heroicon-o-rectangle-stack')
            ->sort($item->sort_order);

        if ($badge = $item->badge) {
            $navigationItem->badge($badge);
        }

        $children = $item->children()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (AdminNavigationItem $child) => $this->buildNavigationItem($child))
            ->filter()
            ->values()
            ->all();

        $url = $item->resolveUrl();

        if ($item->type === 'resource' && class_exists($item->target)) {
            if (method_exists($item->target, 'canViewAny') && ! $item->target::canViewAny()) {
                return null;
            }
        }

        if ($url) {
            $navigationItem->url($url);
        } elseif (empty($children)) {
            return null;
        }

        if (! empty($children)) {
            $navigationItem->items($children);
        }

        return $navigationItem;
    }
}
