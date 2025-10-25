<div
    x-data="sidebarPreferencesComponent()"
    x-init="init()"
    class="mt-4 border-t border-gray-200 px-3 py-4 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300"
>
    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        Tùy chọn Sidebar
    </p>

    <div class="mt-3 grid grid-cols-2 gap-2">
        <button
            type="button"
            @click="setMode('auto')"
            :class="buttonClasses('auto')"
            class="flex items-center gap-2 rounded-lg border px-2 py-2 text-left text-xs font-medium transition"
        >
            <x-heroicon-o-arrow-path-rounded-square class="h-4 w-4" />
            <span>Tự động ẩn</span>
        </button>

        <button
            type="button"
            @click="setMode('expanded')"
            :class="buttonClasses('expanded')"
            class="flex items-center gap-2 rounded-lg border px-2 py-2 text-left text-xs font-medium transition"
        >
            <x-heroicon-o-arrows-pointing-out class="h-4 w-4" />
            <span>Mở rộng</span>
        </button>

        <button
            type="button"
            @click="setMode('collapsed')"
            :class="buttonClasses('collapsed')"
            class="flex items-center gap-2 rounded-lg border px-2 py-2 text-left text-xs font-medium transition"
        >
            <x-heroicon-o-bars-3-bottom-left class="h-4 w-4" />
            <span>Thu gọn</span>
        </button>

        <button
            type="button"
            @click="setMode('locked')"
            :class="buttonClasses('locked')"
            class="flex items-center gap-2 rounded-lg border px-2 py-2 text-left text-xs font-medium transition"
        >
            <x-heroicon-o-lock-closed class="h-4 w-4" />
            <span>Khóa hiển thị</span>
        </button>
    </div>

    <p class="mt-3 text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
        Thiết lập này được lưu trên trình duyệt để ghi nhớ cách hiển thị sidebar cho lần truy cập tiếp theo.
    </p>
</div>
