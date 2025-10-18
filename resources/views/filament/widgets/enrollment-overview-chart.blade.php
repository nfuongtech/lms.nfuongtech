<x-filament::card>
    <x-slot name="header">
        <div class="text-base font-semibold">Thống kê Học viên theo tháng</div>
    </x-slot>

    <div class="text-sm text-gray-600">
        Biểu đồ hiển thị ở widget <code>ThongKeHocVienChart</code> (ChartWidget). File blade này chỉ để giữ bố cục/tùy biến.
    </div>

    @include('filament.widgets.partials.dashboard-chart-script')
</x-filament::card>
