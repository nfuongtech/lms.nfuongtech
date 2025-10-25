<?php

namespace App\Livewire;

use App\Models\AdminNavigationSetting;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminSidebarPreferences extends Component
{
    public const MODES = ['auto', 'expanded', 'collapsed', 'locked'];

    public string $mode = 'auto';

    public function mount(): void
    {
        $this->mode = AdminNavigationSetting::instance()->sidebar_mode;
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
                'auto' => 'Tự động ẩn',
                'expanded' => 'Mở rộng',
                'collapsed' => 'Thu gọn',
                'locked' => 'Khóa hiển thị',
            ],
        ]);
    }
}
