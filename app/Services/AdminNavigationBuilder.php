<?php

namespace App\Services;

use App\Models\AdminNavigationItem;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AdminNavigationBuilder
{
    public static function build(NavigationBuilder $builder): NavigationBuilder
    {
        if (! Schema::hasTable((new AdminNavigationItem())->getTable())) {
            return $builder;
        }

        $groups = static::resolveGroups();

        if ($groups->isEmpty()) {
            return $builder;
        }

        if (method_exists($builder, 'groups')) {
            $builder->groups($groups->all());
        }

        return $builder;
    }

    protected static function resolveGroups(): Collection
    {
        return AdminNavigationItem::query()
            ->where('type', 'group')
            ->whereNull('parent_id')
            ->active()
            ->ordered()
            ->with(['children' => fn ($query) => $query->active()->ordered()])
            ->get()
            ->map(function (AdminNavigationItem $group) {
                $items = $group->children->map(function (AdminNavigationItem $item) {
                    return static::makeNavigationItem($item);
                })->filter()->values();

                if ($items->isEmpty()) {
                    return null;
                }

                $navigationGroup = NavigationGroup::make()
                    ->label($group->title);

                if ($group->icon) {
                    $navigationGroup->icon($group->icon);
                }

                return $navigationGroup->items($items->all());
            })
            ->filter()
            ->values();
    }

    protected static function makeNavigationItem(AdminNavigationItem $item): ?NavigationItem
    {
        $label = $item->title;
        $icon = $item->icon ?: 'heroicon-o-square-2-stack';

        return match ($item->type) {
            'resource' => static::resourceItem($item, $label, $icon),
            'page' => static::pageItem($item, $label, $icon),
            'url' => static::urlItem($item, $label, $icon),
            default => null,
        };
    }

    protected static function resourceItem(AdminNavigationItem $item, string $label, string $icon): ?NavigationItem
    {
        $class = $item->target;

        if (! is_string($class) || ! class_exists($class) || ! is_subclass_of($class, Resource::class)) {
            return null;
        }

        if (method_exists($class, 'canViewAny') && ! $class::canViewAny()) {
            return null;
        }

        $url = method_exists($class, 'getUrl')
            ? $class::getUrl(panel: 'admin')
            : null;

        if (! $url) {
            return null;
        }

        $navigation = NavigationItem::make($label)->url($url);

        if ($icon) {
            $navigation->icon($icon);
        }

        if (method_exists($class, 'getNavigationBadge')) {
            $badge = $class::getNavigationBadge();
            if ($badge !== null) {
                $navigation->badge($badge);
            }
        }

        if (method_exists($class, 'getNavigationBadgeColor')) {
            $color = $class::getNavigationBadgeColor();
            if ($color) {
                $navigation->badgeColor($color);
            }
        }

        if (method_exists($class, 'getNavigationBadgeTooltip')) {
            $tooltip = $class::getNavigationBadgeTooltip();
            if ($tooltip) {
                $navigation->badgeTooltip($tooltip);
            }
        }

        if (method_exists($class, 'getNavigationIcon') && ! $item->icon && $class::getNavigationIcon()) {
            $navigation->icon($class::getNavigationIcon());
        }

        if (method_exists($class, 'getNavigationLabel')) {
            $navigation->label($item->title ?: $class::getNavigationLabel());
        }

        $routeName = method_exists($class, 'getNavigationRouteName')
            ? $class::getNavigationRouteName()
            : null;

        if ($routeName) {
            $navigation->isActiveWhen(fn (): bool => request()->routeIs($routeName) || request()->routeIs($routeName . '.*'));
        }

        return $navigation;
    }

    protected static function pageItem(AdminNavigationItem $item, string $label, string $icon): ?NavigationItem
    {
        $class = $item->target;

        if (! is_string($class) || ! class_exists($class) || ! is_subclass_of($class, Page::class)) {
            return null;
        }

        if (method_exists($class, 'canView') && ! $class::canView()) {
            return null;
        }

        $url = method_exists($class, 'getUrl')
            ? $class::getUrl(panel: 'admin')
            : null;

        if (! $url) {
            return null;
        }

        $navigation = NavigationItem::make($label)->url($url);

        if ($icon) {
            $navigation->icon($icon);
        }

        if (method_exists($class, 'getNavigationIcon') && ! $item->icon && $class::getNavigationIcon()) {
            $navigation->icon($class::getNavigationIcon());
        }

        if (method_exists($class, 'getNavigationLabel')) {
            $navigation->label($item->title ?: $class::getNavigationLabel());
        }

        $routeName = method_exists($class, 'getNavigationRouteName')
            ? $class::getNavigationRouteName()
            : null;

        if ($routeName) {
            $navigation->isActiveWhen(fn (): bool => request()->routeIs($routeName) || request()->routeIs($routeName . '.*'));
        }

        return $navigation;
    }

    protected static function urlItem(AdminNavigationItem $item, string $label, string $icon): NavigationItem
    {
        $navigation = NavigationItem::make($label)->url($item->url ?? '#');

        if ($icon) {
            $navigation->icon($icon);
        }

        return $navigation;
    }
}
