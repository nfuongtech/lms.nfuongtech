<?php

namespace App\Livewire\Admin;

use App\Models\AdminSidebarPreference;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SidebarModeSwitcher extends Component
{
    public string $mode = 'auto';

    public function mount(): void
    {
        $user = Auth::user();

        if ($user && $user->sidebarPreference) {
            $this->mode = $user->sidebarPreference->mode;
        } else {
            $this->mode = Arr::first(AdminSidebarPreference::MODES);
        }
    }

    public function setMode(string $mode): void
    {
        if (! in_array($mode, AdminSidebarPreference::MODES, true)) {
            return;
        }

        $this->mode = $mode;

        $user = Auth::user();

        if ($user) {
            $user->sidebarPreference()->updateOrCreate([], [
                'mode' => $mode,
            ]);
        }

        $this->dispatch('admin-sidebar-mode-updated', mode: $mode);
    }

    public function render()
    {
        return view('livewire.admin.sidebar-mode-switcher');
    }
}
