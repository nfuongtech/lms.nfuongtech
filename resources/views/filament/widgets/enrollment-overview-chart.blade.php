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

    @php($summary = $this->getSummaryTableData())

    <div class="mt-8">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                Bảng thống kê học viên theo tháng
            </h3>
            @if($summary['displayMode'] === 'monthly')
                <span class="text-xs text-gray-500">Năm {{ $summary['year'] }}</span>
            @elseif($summary['month'])
                <span class="text-xs text-gray-500">
                    {{ sprintf('Tháng %02d/%d', $summary['month'], $summary['year']) }}
                </span>
            @endif
        </div>

        @if($summary['hasData'])
            @if(count($summary['labels']) > 1)
                <div class="mt-3 overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-[640px] divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Loại</th>
                                @foreach($summary['labels'] as $label)
                                    <th class="px-3 py-3 text-center font-semibold text-gray-600">{{ $label }}</th>
                                @endforeach
                                <th class="px-4 py-3 text-right font-semibold text-gray-600">Tổng</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($summary['rows'] as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $row['label'] }}</td>
                                    @foreach($row['values'] as $value)
                                        <td class="px-3 py-3 text-center text-gray-700">
                                            {{ number_format((int) $value, 0, ',', '.') }}
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                        {{ number_format((int) $row['total'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tổng theo tháng</th>
                                @foreach($summary['columnTotals'] as $total)
                                    <th class="px-3 py-3 text-center font-semibold text-gray-700">
                                        {{ number_format((int) $total, 0, ',', '.') }}
                                    </th>
                                @endforeach
                                <th class="px-4 py-3 text-right font-semibold text-gray-800">
                                    {{ number_format((int) $summary['grandTotal'], 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="mt-3 overflow-hidden rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Hạng mục</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-600">Số lượng</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($summary['rows'] as $row)
                                <tr class="@if(!empty($row['is_emphasis'])) bg-gray-50 font-semibold text-gray-800 @else hover:bg-gray-50 text-gray-700 @endif">
                                    <td class="px-4 py-3">{{ $row['label'] }}</td>
                                    <td class="px-4 py-3 text-right">
                                        {{ number_format((int) ($row['values'][0] ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tổng số học viên</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-800">
                                    {{ number_format((int) ($summary['columnTotals'][0] ?? 0), 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        @else
            <p class="mt-3 rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500">
                Chưa có dữ liệu thống kê cho bộ lọc hiện tại.
            </p>
        @endif
    </div>

    @include('filament.widgets.partials.dashboard-chart-script')
</x-filament::card>
