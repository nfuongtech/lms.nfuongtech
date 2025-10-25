<?php

namespace App\Livewire;

use App\Models\AdminNavigationSetting;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminSidebarPreferences extends Component
{
    public const MODES = ['hidden', 'hover', 'pinned'];

    public string $mode = 'pinned';

    public function mount(): void
    {
        $setting = AdminNavigationSetting::instance();
        $this->mode = in_array($setting->sidebar_mode, self::MODES, true)
            ? $setting->sidebar_mode
            : 'pinned';
    }

    public function setMode(string $mode): void
    {
        if (! in_array($mode, self::MODES, true)) {
            return;
        }

        $setting = AdminNavigationSetting::instance();
        $setting->sidebar_mode = $mode;
        $setting->save();

        $this->mode = $mode;

        $this->dispatch('sidebar-mode-changed', mode: $mode);
    }

    #[On('sidebar-mode-sync')]
    public function sync(string $mode): void
    {
        if (in_array($mode, self::MODES, true)) {
            $this->mode = $mode;
        }
    }

    public function render()
    {
        return view('livewire.admin-sidebar-preferences', [
            'modes' => [
                'hidden' => 'Tự động ẩn',
                'hover' => 'Hiện khi rê chuột',
                'pinned' => 'Luôn hiển thị',
            ],
        ]);
    }
}
