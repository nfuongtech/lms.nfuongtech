<x-filament-panels::page>
    {{ $this->form }}

    @if($selectedKhoaHoc)
        <div class="mt-4">
            <h2 class="text-lg font-semibold">Danh sách học viên</h2>
            {{ $this->table }}
        </div>
    @endif
</x-filament-panels::page>
