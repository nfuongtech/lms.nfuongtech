{{-- resources/views/filament/components/sidebar-mode-toggle.blade.php --}}
<div id="nf-toolbar-group" class="inline-flex items-center ms-2 nf-toolbar-group"
     x-data="sidebarModeToggle()"
     x-init="init()">
    {{-- Trạng thái hiện hành bên trái icon (không tooltip) --}}
    <span class="nf-left-status" x-text="label"></span>

    {{-- Nút icon đổi chế độ --}}
    <div class="nf-icon-wrap">
        <button type="button"
                class="nf-sidebar-toggle"
                aria-label="Chế độ Slidebar"
                @click="nextMode()">
            <template x-if="mode === 'always'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M4 5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4zM7 7h3v10H7V7z"/>
                </svg>
            </template>
            <template x-if="mode === 'auto'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M3 7a2 2 0 0 1 2-2h9v14H5a2 2 0 0 1-2-2V7zM16 5h5a1 1 0 0 1 1 1v5h-2V7h-4V5z"/>
                    <path d="M19 13a4 4 0 1 1-3.999 4A4 4 0 0 1 19 13zm0 2a1 1 0 0 1 1 1v1.2l.8.8a1 1 0 1 1-1.4 1.4l-1-1A1 1 0 0 1 18 18v-2a1 1 0 0 1 1-1z"/>
                </svg>
            </template>
            <template x-if="mode === 'hidden'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M4 5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h8v-2H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6V5H4z"/>
                    <path d="M20.3 3.7a1 1 0 0 0-1.4 0L3.7 18a1 1 0 1 0 1.4 1.4L20.3 5.1a1 1 0 0 0 0-1.4z"/>
                </svg>
            </template>
        </button>
    </div>
</div>
