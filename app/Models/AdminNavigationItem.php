<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string $handle
 * @property string $type
 * @property string|null $target
 * @property string|null $external_url
 * @property string|null $navigation_group
 * @property int|null $parent_id
 * @property int $sort
 * @property bool $is_active
 * @property string $icon_source
 * @property string|null $icon_name
 * @property string|null $icon_path
 * @property bool $open_in_new_tab
 * @property array|null $meta
 */
class AdminNavigationItem extends Model
{
    protected $fillable = [
        'title',
        'handle',
        'type',
        'target',
        'external_url',
        'navigation_group',
        'parent_id',
        'sort',
        'is_active',
        'icon_source',
        'icon_name',
        'icon_path',
        'open_in_new_tab',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'open_in_new_tab' => 'boolean',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (AdminNavigationItem $item) {
            if (blank($item->handle)) {
                $item->handle = self::generateHandle($item->title);
            }

            $item->sort ??= self::nextSortOrder($item->parent_id);
        });

        static::saving(function (AdminNavigationItem $item) {
            if (is_null($item->sort)) {
                $item->sort = self::nextSortOrder($item->parent_id);
            }
        });
    }

    protected static function nextSortOrder(?int $parentId): int
    {
        $max = self::query()
            ->where('parent_id', $parentId)
            ->max('sort');

        return is_null($max) ? 0 : $max + 10;
    }

    public static function generateHandle(string $title): string
    {
        $base = Str::slug($title);
        $handle = $base;
        $suffix = 1;

        while (self::where('handle', $handle)->exists()) {
            $handle = $base . '-' . $suffix++;
        }

        return $handle;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('title');
    }

    public function iconAlias(): ?string
    {
        if ($this->icon_source === 'upload' && $this->icon_path) {
            $name = pathinfo($this->icon_path, PATHINFO_FILENAME);
            return $name ? 'admin-menu-uploads::' . $name : null;
        }

        if ($this->icon_source === 'heroicon' && filled($this->icon_name)) {
            return $this->icon_name;
        }

        if ($this->icon_source === 'custom' && filled($this->icon_name)) {
            return $this->icon_name;
        }

        return null;
    }

    public function effectiveGroup(): string
    {
        return $this->navigation_group ?: __('Khác');
    }

    public function moveUp(): void
    {
        $previous = static::query()
            ->where('parent_id', $this->parent_id)
            ->where('id', '!=', $this->id)
            ->where('sort', '<', $this->sort)
            ->orderByDesc('sort')
            ->first();

        if (! $previous) {
            return;
        }

        $currentSort = $this->sort;
        $this->update(['sort' => $previous->sort]);
        $previous->update(['sort' => $currentSort]);
    }

    public function moveDown(): void
    {
        $next = static::query()
            ->where('parent_id', $this->parent_id)
            ->where('id', '!=', $this->id)
            ->where('sort', '>', $this->sort)
            ->orderBy('sort')
            ->first();

        if (! $next) {
            return;
        }

        $currentSort = $this->sort;
        $this->update(['sort' => $next->sort]);
        $next->update(['sort' => $currentSort]);
    }

    public function toArrayForExport(): array
    {
        return Arr::except($this->toArray(), ['id', 'created_at', 'updated_at']);
    }
}
