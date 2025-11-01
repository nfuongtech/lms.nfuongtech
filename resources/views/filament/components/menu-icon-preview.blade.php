@php
    $iconType = $get('icon_type');
    $icon = $get('icon');
    $iconPath = $get('icon_path');
@endphp

<div class="flex items-center gap-3 rounded-lg border border-slate-200/70 p-3 dark:border-slate-700/70">
    @if ($iconType === 'upload' && filled($iconPath))
        <div class="h-10 w-10 flex items-center justify-center overflow-hidden rounded-md bg-slate-50 dark:bg-slate-900">
            <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('filament.assets_disk', 'public'))->url($iconPath) }}" alt="Icon preview" class="h-10 w-10 object-contain" />
        </div>
        <div class="text-sm text-slate-600 dark:text-slate-300">
            <p class="font-medium">Icon tải lên</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">Đường dẫn: {{ $iconPath }}</p>
        </div>
    @elseif(filled($icon))
        <div class="h-10 w-10 flex items-center justify-center rounded-md bg-slate-50 text-slate-700 dark:bg-slate-900 dark:text-slate-200">
            <x-dynamic-component :component="'blade-heroicon-o-' . str($icon)->after('heroicon-o-')" class="h-6 w-6" />
        </div>
        <div class="text-sm text-slate-600 dark:text-slate-300">
            <p class="font-medium">Icon Heroicon</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $icon }}</p>
        </div>
    @else
        <div class="text-sm text-slate-500 dark:text-slate-400">
            Chưa chọn icon cho menu này.
        </div>
    @endif
</div>
