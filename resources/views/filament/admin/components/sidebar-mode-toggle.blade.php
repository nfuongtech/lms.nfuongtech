<div
    data-sidebar-mode-control
    class="fi-topbar-item hidden items-center gap-3 lg:flex"
    wire:ignore
>
    <span class="fi-sidebar-mode-title">Sidebar</span>
    <span class="fi-sidebar-mode-active" data-sidebar-mode-label>Đang: Tự động ẩn</span>
    <div class="fi-sidebar-mode-select-wrapper">
        <label for="sidebar-mode-select" class="sr-only">Chế độ hiển thị sidebar</label>
        <select id="sidebar-mode-select" data-sidebar-mode-select class="fi-sidebar-mode-select">
            <option value="auto" selected>Tự động ẩn</option>
            <option value="hover">Hiện khi rê chuột</option>
            <option value="visible">Luôn hiển thị</option>
            <option value="hidden">Ẩn hoàn toàn</option>
        </select>
        <svg class="fi-sidebar-mode-select-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </div>
</div>

@once
    @vite('resources/js/filament/admin/sidebar.js')
@endonce
