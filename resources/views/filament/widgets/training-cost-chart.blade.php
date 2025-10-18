<x-filament::card>
    <x-slot name="header">
        <div class="space-y-1">
            <div class="text-base font-semibold">Chi phí đào tạo theo tháng</div>
            <p class="text-sm text-gray-600">
                Sử dụng bộ lọc Năm (kế hoạch) và tùy chọn nhiều Loại hình đào tạo để theo dõi tổng chi phí phát sinh mỗi tháng
                từ danh sách học viên hoàn thành.
            </p>
        </div>
    </x-slot>

    <div class="mt-6">
        {{ $this->chart }}
    </div>

    @include('filament.widgets.partials.dashboard-chart-script')
</x-filament::card>
