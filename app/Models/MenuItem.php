<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $label
 * @property string|null $icon
 * @property string $url
 * @property int|null $parent_id
 * @property int $order
 */
class MenuItem extends Model
{
    protected $fillable = [
        'label',
        'icon',
        'url',
        'parent_id',
        'order',
    ];

    /**
     * Scope a query to order menu items by the stored order.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    /**
     * Retrieve menu data as a nested array structure.
     */
    public static function toTree(): array
    {
        /** @var Collection<int, self> $items */
        $items = static::query()->ordered()->get();
        $grouped = $items->groupBy('parent_id');

        $build = function (?int $parentId = null) use (&$build, $grouped): array {
            return ($grouped[$parentId] ?? collect())
                ->map(fn (self $item) => [
                    'id' => $item->id,
                    'label' => $item->label,
                    'icon' => $item->icon,
                    'url' => $item->url,
                    'children' => $build($item->id),
                ])
                ->values()
                ->toArray();
        };

        return $build();
    }

    /**
     * Persist menu data provided from the configuration form.
     */
    public static function syncFromArray(array $menus): void
    {
        $usedIds = [];
        $position = 0;

        $persist = function (array $items, ?int $parentId) use (&$persist, &$usedIds, &$position): void {
            foreach ($items as $item) {
                $position++;

                $model = null;
                if (! empty($item['id'])) {
                    $model = static::query()->find($item['id']);
                }

                if (! $model) {
                    $model = new static();
                }

                $model->fill([
                    'label' => $item['label'] ?? 'Menu',
                    'icon' => $item['icon'] ?? null,
                    'url' => $item['url'] ?? '#',
                ]);

                $model->parent_id = $parentId;
                $model->order = $position;
                $model->save();

                $usedIds[] = $model->id;

                $children = $item['children'] ?? [];
                $persist($children, $model->id);
            }
        };

        $persist($menus, null);

        if (count($usedIds) > 0) {
            static::query()->whereNotIn('id', $usedIds)->delete();
        } else {
            static::query()->delete();
        }
    }
}
