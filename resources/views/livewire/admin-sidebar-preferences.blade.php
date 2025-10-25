<div class="fi-sidebar-preferences">
    <span class="fi-sidebar-preferences__label">Tùy chọn hiển thị</span>
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
                <span class="fi-sidebar-preferences__button-label">{{ $label }}</span>
            </button>
        @endforeach
    </div>
</div>
