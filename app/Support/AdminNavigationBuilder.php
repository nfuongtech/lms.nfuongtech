<?php

namespace App\Support;

use App\Models\AdminNavigationItem;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem as FilamentNavigationItem;
use Filament\Resources\Resource;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminNavigationBuilder
{
    public function build(NavigationBuilder $builder): NavigationBuilder
    {
        $records = AdminNavigationItem::query()
            ->with(['children' => fn ($query) => $query->ordered()])
            ->ordered()
            ->get();

        if ($records->isEmpty()) {
            return $builder;
        }

        $groups = $records
            ->filter(fn (AdminNavigationItem $item) => $item->parent_id === null)
            ->groupBy(fn (AdminNavigationItem $item) => $item->effectiveGroup())
            ->map(function (Collection $items, string $groupLabel) {
                return NavigationGroup::make()
                    ->label($groupLabel)
                    ->items($items->map(fn (AdminNavigationItem $item) => $this->toNavigationItem($item))->all());
            })
            ->values()
            ->all();

        return $builder->groups($groups);
    }

    protected function toNavigationItem(AdminNavigationItem $record): FilamentNavigationItem
    {
        $navigationItem = FilamentNavigationItem::make($record->title)
            ->sort($record->sort)
            ->visible(fn () => $record->is_active)
            ->icon($record->iconAlias());

        if ($record->children->isNotEmpty()) {
            $navigationItem->children(
                $record->children
                    ->map(fn (AdminNavigationItem $child) => $this->toNavigationItem($child))
                    ->all()
            );

            return $navigationItem->collapsed(false);
        }

        $url = $this->resolveUrl($record) ?: '#';

        $navigationItem->url(fn () => $url, shouldOpenInNewTab: $record->open_in_new_tab);
        $navigationItem->isActiveWhen(fn () => $this->isActive($record));

        return $navigationItem;
    }

    protected function resolveUrl(AdminNavigationItem $record): ?string
    {
        return match ($record->type) {
            'resource' => $this->resolveResourceUrl($record->target),
            'page'     => $this->resolvePageUrl($record->target),
            'link'     => $record->external_url,
            default    => null,
        };
    }

    protected function resolveResourceUrl(?string $class): ?string
    {
        if (! $class || ! class_exists($class) || ! is_subclass_of($class, Resource::class)) {
            return null;
        }

        return $class::getUrl();
    }

    protected function resolvePageUrl(?string $class): ?string
    {
        if (! $class || ! class_exists($class) || ! is_subclass_of($class, Page::class)) {
            return null;
        }

        return $class::getUrl();
    }

    protected function isActive(AdminNavigationItem $record): bool
    {
        if ($record->type === 'link') {
            return false;
        }

        $target = $record->target;

        if ($record->type === 'resource' && class_exists($target) && is_subclass_of($target, Resource::class)) {
            $base = $target::getRouteBaseName();

            return Str::of(request()->route()?->getName())->startsWith($base);
        }

        if ($record->type === 'page' && class_exists($target) && is_subclass_of($target, Page::class)) {
            $name = $target::getRouteName();

            return request()->routeIs($name);
        }

        return false;
    }
}
