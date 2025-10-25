<div class="fi-sidebar-inline-toggle">
    <button
        type="button"
        class="fi-sidebar-toggle"
        data-sidebar-toggle
        data-open-label="Mở thanh điều hướng"
        data-close-label="Ẩn thanh điều hướng"
        aria-label="Ẩn thanh điều hướng"
    >
        <x-filament::icon icon="heroicon-o-bars-3" class="fi-sidebar-toggle__icon" data-sidebar-toggle-icon="closed" />
        <x-filament::icon icon="heroicon-o-chevron-left" class="fi-sidebar-toggle__icon" data-sidebar-toggle-icon="open" />
    </button>
</div>
<div class="fi-sidebar-preferences__wrapper">
    <livewire:admin-sidebar-preferences />
</div>
