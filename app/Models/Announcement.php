<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Announcement extends Model
{
    protected $fillable = [
        'title','slug','content',
        'publish_at','expires_at','status',
        'is_featured','is_popup','enable_marquee','scroll_speed',
        'cover_path','video_path','video_url','redirect_url',
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_popup' => 'boolean',
        'enable_marquee' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug(Str::limit($model->title, 80, ''));
            }
        });
    }

    public function scopeOrdered(Builder $q): Builder
    {
        // Thứ tự: Dự thảo → Đang áp dụng → Hết hiệu lực; “mới nhất trước”
        return $q->orderByRaw("FIELD(status,'draft','active','expired')")
                 ->orderByDesc('publish_at')
                 ->orderByDesc('created_at');
    }

    public function scopeActive(Builder $q): Builder
    {
        $now = Carbon::now();
        return $q->where('status','active')
                 ->where(function ($q) use ($now) {
                     $q->whereNull('expires_at')->orWhere('expires_at','>=',$now);
                 })
                 ->where(function ($q) use ($now) {
                     $q->whereNull('publish_at')->orWhere('publish_at','<=',$now);
                 });
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getIsLiveAttribute(): bool
    {
        return $this->status === 'active' && !$this->is_expired &&
            (is_null($this->publish_at) || $this->publish_at->isPast());
    }
}
