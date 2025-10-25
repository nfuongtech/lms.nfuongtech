<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminNavigationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'label',
        'icon',
        'type',
        'target',
        'badge',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

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
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function resolveUrl(): ?string
    {
        if (! $this->target) {
            return null;
        }

        return match ($this->type) {
            'url' => $this->target,
            'route' => $this->resolveRouteUrl(),
            'resource' => $this->resolveResourceUrl(),
            default => $this->target,
        };
    }

    protected function resolveResourceUrl(): ?string
    {
        if (! class_exists($this->target)) {
            return null;
        }

        if (! method_exists($this->target, 'getUrl')) {
            return null;
        }

        return $this->target::getUrl();
    }

    protected function resolveRouteUrl(): ?string
    {
        try {
            return route($this->target);
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
