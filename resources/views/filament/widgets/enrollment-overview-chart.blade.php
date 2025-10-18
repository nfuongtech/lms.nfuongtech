<x-filament::card>
    <x-slot name="header">
        <div class="space-y-1">
            <div class="text-base font-semibold">Thống kê Học viên theo tháng</div>
            <p class="text-sm text-gray-600">
                Lọc theo Năm (kế hoạch) để xem biểu đồ cột có màu sắc hiển thị trực tiếp số liệu Đăng ký, Hoàn thành và Không
                hoàn thành theo từng tháng. Khi chọn thêm Tháng, nhóm Không hoàn thành sẽ được phân tách theo Vắng P, Vắng KP và
                các lý do khác.
            </p>
        </div>
    </x-slot>

    <div class="mt-6">
        {{ $this->chart }}
    </div>

    @include('filament.widgets.partials.dashboard-chart-script')
</x-filament::card>
