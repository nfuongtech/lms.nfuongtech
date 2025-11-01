@php
    /** @var \App\Models\MenuItem $record */
    $record = $getRecord();
    $icon = $record->getIconForDisplay();
@endphp

<div class="flex items-center justify-center">
    @if ($record->usesUploadedIcon() && $icon)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('filament.assets_disk', 'public'))->url($icon) }}" alt="Icon" class="h-6 w-6 object-contain" />
    @elseif ($icon)
        <x-dynamic-component :component="'blade-heroicon-o-' . str($icon)->after('heroicon-o-')" class="h-6 w-6 text-slate-600 dark:text-slate-200" />
    @else
        <span class="text-xs text-slate-400">—</span>
    @endif
</div>
