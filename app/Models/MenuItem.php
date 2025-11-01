<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            if (! $item->sort) {
                $item->sort = (int) static::query()
                    ->where('parent_id', $item->parent_id)
                    ->max('sort') + 1;
            }
        });
    }

    protected $fillable = [
        'parent_id',
        'label',
        'type',
        'target',
        'url',
        'icon_type',
        'icon',
        'icon_path',
        'sort',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function siblings(): Builder
    {
        return static::query()
            ->where('parent_id', $this->parent_id)
            ->whereKeyNot($this->getKey());
    }

    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public function usesUploadedIcon(): bool
    {
        return $this->icon_type === 'upload' && filled($this->icon_path);
    }

    public function getIconForDisplay(): ?string
    {
        if ($this->usesUploadedIcon()) {
            return $this->icon_path;
        }

        return $this->icon ?: null;
    }

    public function getResolvedUrl(): ?string
    {
        if ($this->type === 'url') {
            return $this->url ?: null;
        }

        return null;
    }

    public function moveOrder(int $direction): void
    {
        $siblings = static::query()
            ->where('parent_id', $this->parent_id)
            ->orderBy('sort')
            ->get();

        $currentIndex = $siblings->search(fn (self $item) => $item->is($this));

        if ($currentIndex === false) {
            return;
        }

        $targetIndex = $currentIndex + $direction;

        if ($targetIndex < 0 || $targetIndex >= $siblings->count()) {
            return;
        }

        /** @var self $target */
        $target = $siblings[$targetIndex];

        $currentSort = $this->sort;
        $this->sort = $target->sort;
        $this->save();

        $target->sort = $currentSort;
        $target->save();
    }
}
