<x-filament::page>
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            {{ $this->getHeading() }}
        </h1>

        @php($selectedCourse = $this->filterState['course_id'] ?? null)

        @if($this->summaryRows->isNotEmpty())
            <div class="bg-white shadow rounded-lg">
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <h2 class="text-base font-semibold text-gray-900">Tổng quan khóa học</h2>
                    <p class="text-sm text-gray-500">Nhấn vào hàng để xem chi tiết học viên hoàn thành theo khóa học.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-center font-semibold">TT</th>
                                <th class="px-3 py-2 font-semibold">Mã khóa</th>
                                <th class="px-3 py-2 font-semibold">Tên khóa học</th>
                                <th class="px-3 py-2 text-center font-semibold">Tổng số giờ</th>
                                <th class="px-3 py-2 font-semibold">Giảng viên</th>
                                <th class="px-3 py-2 font-semibold">Thời gian đào tạo</th>
                                <th class="px-3 py-2 text-center font-semibold">Số lượng HV</th>
                                <th class="px-3 py-2 text-center font-semibold">Hoàn thành</th>
                                <th class="px-3 py-2 text-center font-semibold">Không hoàn thành</th>
                                <th class="px-3 py-2 text-center font-semibold">Tổng thu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($this->summaryRows as $row)
                                <tr
                                    wire:key="summary-{{ $row['id'] }}"
                                    wire:click="selectCourseFromSummary({{ $row['id'] }})"
                                    class="cursor-pointer transition hover:bg-primary-50 {{ $selectedCourse === $row['id'] ? 'bg-primary-50' : 'bg-white' }}"
                                >
                                    <td class="px-3 py-2 text-center font-medium text-gray-900">{{ $row['index'] }}</td>
                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $row['ma_khoa'] }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $row['ten_khoa'] }}</td>
                                    <td class="px-3 py-2 text-center text-gray-700">{{ $row['tong_gio'] }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $row['giang_vien'] }}</td>
                                    <td class="px-3 py-2 text-gray-700 whitespace-pre-line">{{ $row['thoi_gian'] }}</td>
                                    <td class="px-3 py-2 text-center text-gray-700">{{ number_format($row['so_luong_hv'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-center text-emerald-600 font-semibold">{{ number_format($row['hoan_thanh'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-center text-rose-600 font-semibold">{{ number_format($row['khong_hoan_thanh'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-center text-gray-700">{{ $row['tong_thu'] > 0 ? number_format($row['tong_thu'], 0, ',', '.') : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{ $this->table }}
    </div>
</x-filament::page>
