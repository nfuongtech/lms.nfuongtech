<?php

namespace App\Support;

use App\Models\MenuItem;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Storage;

class MenuNavigationBuilder
{
    public function build(NavigationBuilder $builder): NavigationBuilder
    {
        if (MenuItem::query()->active()->count() === 0) {
            return $builder;
        }

        $groupsOutput = [];
        $standaloneItems = [];

        $topLevelItems = MenuItem::query()
            ->active()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->get();

        foreach ($topLevelItems as $group) {
            if ($group->isGroup()) {
                $items = $group->children()
                    ->active()
                    ->get()
                    ->map(fn (MenuItem $item) => $this->buildItem($item))
                    ->filter()
                    ->all();

                $groupsOutput[] = NavigationGroup::make()
                    ->label($group->label)
                    ->icon($group->usesUploadedIcon() ? null : $group->icon)
                    ->items($items);

                continue;
            }

            $item = $this->buildItem($group);

            if ($item) {
                $standaloneItems[] = $item;
            }
        }

        return $builder
            ->groups($groupsOutput)
            ->items($standaloneItems);
    }

    protected function buildItem(MenuItem $item): ?NavigationItem
    {
        if (! $item->is_active) {
            return null;
        }

        $navigationItem = NavigationItem::make($item->label)
            ->icon($item->usesUploadedIcon() ? null : $item->icon)
            ->sort($item->sort);

        if ($item->usesUploadedIcon()) {
            $navigationItem->extraAttributes([
                'data-custom-icon' => Storage::disk(config('filament.assets_disk', 'public'))->url($item->icon_path),
            ]);
        }

        switch ($item->type) {
            case 'resource':
            case 'page':
                if ($item->target && class_exists($item->target) && method_exists($item->target, 'getUrl')) {
                    $navigationItem->url($item->target::getUrl());
                }

                break;
            case 'url':
                if ($item->url) {
                    $navigationItem->url($item->url);
                }

                break;
        }

        return $navigationItem;
    }
}
