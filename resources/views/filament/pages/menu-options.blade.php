<x-filament::page>
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-end gap-3">
            <x-filament::button type="submit" icon="heroicon-o-check-circle">
                Lưu cấu hình
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
