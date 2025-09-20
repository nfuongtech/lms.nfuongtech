{{-- resources/views/filament/widgets/thong-ke-hoc-vien-widget.blade.php --}}
<x-filament::widget>
    <x-filament::card>
        <x-slot name="header">
            <h2 class="text-lg font-bold text-gray-800">Thống kê số lượng học viên theo đơn vị</h2>
        </x-slot>

        <div class="mt-4">
            @if(!empty($thongKe) && count($thongKe))
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">THACO/TĐTV</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Công ty/Ban NVQT</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Số lượng HV<br><span class="text-gray-400">(Đang làm việc)</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($thongKe as $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-4 py-3 text-sm text-gray-700 text-center font-medium">{{ $item['stt'] }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item['thaco_tdtv'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item['cong_ty_ban_nvqt'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 text-center font-semibold">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                            {{ $item['so_luong'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-6 text-gray-500 bg-gray-50 rounded-lg border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.467-.881-6.08-2.33.154-.597.38-.97.68-1.266.21-.21.47-.363.766-.455.08-.025.16-.04.24-.05.02-.004.04-.006.06-.008.01-.001.02-.002.03-.002h.01c.01 0 .02.001.03.002.02.002.04.004.06.008.08.01.16.025.24.05.296.092.556.245.766.455.3.296.526.669.68 1.266z"></path>
                    </svg>
                    <span class="block mt-2">Không có dữ liệu thống kê.</span>
                </div>
            @endif
        </div>
    </x-filament::card>
</x-filament::widget>
