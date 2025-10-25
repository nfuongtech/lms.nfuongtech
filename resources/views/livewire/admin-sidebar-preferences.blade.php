<div class="fi-sidebar-preferences">
    <span class="fi-sidebar-preferences__label">Tùy chọn hiển thị</span>
    @php
        $icons = [
            'hidden' => 'heroicon-o-eye-slash',
            'hover' => 'heroicon-o-cursor-arrow-rays',
            'pinned' => 'heroicon-o-map-pin',
        ];
    @endphp
    <div class="fi-sidebar-preferences__buttons">
        @foreach ($modes as $value => $label)
            <button
                type="button"
                wire:click="setMode('{{ $value }}')"
                data-sidebar-mode-button="{{ $value }}"
                @class([
                    'fi-sidebar-preferences__button',
                    'fi-sidebar-preferences__button--active' => $mode === $value,
                ])
            >
                <x-filament::icon :icon="$icons[$value]" class="fi-sidebar-preferences__button-icon" />
                <span class="fi-sidebar-preferences__button-label">{{ $label }}</span>
            </button>
        @endforeach
    </div>
</div>
