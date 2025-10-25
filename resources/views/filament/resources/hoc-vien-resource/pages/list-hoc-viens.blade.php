{{-- resources/views/filament/resources/hoc-vien-resource/pages/list-hoc-viens.blade.php --}}
<x-filament-panels::page>
    {{-- Hiển thị bảng thống kê --}}
    <div class="bg-white shadow rounded-lg p-5 mb-6">
        <h2 class="text-lg font-semibold mb-4">Thống kê số lượng học viên theo đơn vị</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">THACO/TĐTV</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Công ty/Ban NVQT</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng HV (Đang làm việc)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($thong_ke as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['stt'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['thaco_tdtv'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['cong_ty_ban_nvqt'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['so_luong'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ liệu thống kê.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Hiển thị bảng danh sách học viên như bình thường --}}
    {{ $this->table }}
</x-filament-panels::page>
