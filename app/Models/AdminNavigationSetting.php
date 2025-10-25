<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AdminNavigationSetting extends Model
{
    protected $fillable = [
        'sidebar_mode',
    ];

    public static function instance(): self
    {
        if (! Schema::hasTable((new self())->getTable())) {
            return new self(['sidebar_mode' => 'pinned']);
        }

        $instance = static::query()->firstOrCreate([], [
            'sidebar_mode' => 'pinned',
        ]);

        if (! in_array($instance->sidebar_mode, \App\Livewire\AdminSidebarPreferences::MODES, true)) {
            $instance->sidebar_mode = 'pinned';
            $instance->save();
        }

        return $instance;
    }
}
