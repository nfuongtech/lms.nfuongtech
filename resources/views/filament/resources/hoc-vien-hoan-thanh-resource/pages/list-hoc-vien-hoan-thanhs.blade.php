<x-filament::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">{{ $this->getHeading() }}</h1>
            <div class="flex items-center gap-2 shrink-0">
                @foreach ($this->getCachedHeaderActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </div>

        @php($selectedCourse = $this->filterState['course_id'] ?? null)
        @php($totals = $this->summaryTotals)

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4 border-b space-y-4">
                <h2 class="text-base font-semibold text-gray-900">Tổng quan khóa học</h2>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-500">Năm</label>
                        <select wire:model="filterYear" class="fi-input mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @foreach($this->yearOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-500">Tháng</label>
                        <select wire:model="filterMonth" class="fi-input mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Tất cả</option>
                            @foreach($this->monthOptions as $value => $label)
                                <option value="{{ $value }}">Tháng {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-500">Tuần</label>
                        <select wire:model="filterWeek" class="fi-input mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Tất cả</option>
                            @foreach($this->weekOptions as $value => $label)
                                <option value="{{ $value }}">Tuần {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-500">Từ ngày</label>
                        <input type="date" wire:model="filterFromDate" class="fi-input mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-500">Đến ngày</label>
                        <input type="date" wire:model="filterToDate" class="fi-input mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-500">Loại hình đào tạo</label>
                        <select multiple wire:model="filterTrainingTypes" class="fi-input mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @foreach($this->trainingTypeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 lg:col-span-3 xl:col-span-2">
                        <label class="block text-xs font-semibold uppercase text-gray-500">Khóa học</label>
                        <select wire:model="filterCourseId" class="fi-input mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Tất cả</option>
                            @foreach($this->courseOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Nhấn vào hàng trong bảng bên dưới để xem chi tiết danh sách học viên hoàn thành theo từng khóa học.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-center font-semibold">TT</th>
                            <th class="px-3 py-2 font-semibold">Mã khóa</th>
                            <th class="px-3 py-2 font-semibold">Tên khóa học</th>
                            <th class="px-3 py-2 font-semibold">Trạng thái</th>
                            <th class="px-3 py-2 text-center font-semibold">Tổng số giờ</th>
                            <th class="px-3 py-2 font-semibold">Giảng viên</th>
                            <th class="px-3 py-2 font-semibold">Thời gian đào tạo</th>
                            <th class="px-3 py-2 text-center font-semibold">Số lượng HV</th>
                            <th class="px-3 py-2 text-center font-semibold">Hoàn thành</th>
                            <th class="px-3 py-2 text-center font-semibold">Không hoàn thành</th>
                            <th class="px-3 py-2 text-center font-semibold">Tổng thu</th>
                            <th class="px-3 py-2 font-semibold">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($this->summaryRows as $row)
                            <tr
                                wire:key="summary-{{ $row['id'] }}"
                                wire:click="selectCourseFromSummary({{ $row['id'] }})"
                                class="cursor-pointer transition hover:bg-primary-50 {{ $selectedCourse === $row['id'] ? 'bg-primary-50' : 'bg-white' }}"
                            >
                                <td class="px-3 py-2 text-center font-medium text-gray-900">{{ $row['index'] }}</td>
                                <td class="px-3 py-2 font-medium text-gray-900">{{ $row['ma_khoa'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $row['ten_khoa'] }}</td>
                                <td class="px-3 py-2">
                                    @php
                                        $statusColor = match(Str::slug($row['trang_thai'] ?? '')) {
                                            'tam-hoan' => 'bg-amber-100 text-amber-800',
                                            'ket-thuc' => 'bg-rose-100 text-rose-700',
                                            'dang-dao-tao' => 'bg-blue-100 text-blue-700',
                                            'ban-hanh' => 'bg-emerald-100 text-emerald-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusColor }} whitespace-nowrap">
                                        {{ $row['trang_thai'] ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center text-gray-700">{{ $row['tong_gio'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $row['giang_vien'] }}</td>
                                <td class="px-3 py-2 text-gray-700 whitespace-pre-line">{{ $row['thoi_gian'] }}</td>
                                <td class="px-3 py-2 text-center text-gray-700">{{ number_format($row['so_luong_hv'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-emerald-600 font-semibold">{{ number_format($row['hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-rose-600 font-semibold">{{ number_format($row['khong_hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-gray-700">{{ $row['tong_thu'] > 0 ? number_format($row['tong_thu'], 0, ',', '.') : '-' }}</td>
                                <td class="px-3 py-2 text-gray-600">{{ $row['ghi_chu'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-3 py-4 text-center text-sm text-gray-500">Chưa có khóa học phù hợp với bộ lọc.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($this->summaryRows->isNotEmpty())
                        <tfoot class="bg-slate-50 text-sm font-semibold text-gray-700">
                            <tr>
                                <td colspan="7" class="px-3 py-2 text-right">Tổng cộng</td>
                                <td class="px-3 py-2 text-center">{{ number_format($totals['so_luong_hv'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-emerald-600">{{ number_format($totals['hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-rose-600">{{ number_format($totals['khong_hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center">{{ $totals['tong_thu'] > 0 ? number_format($totals['tong_thu'], 0, ',', '.') : '-' }}</td>
                                <td class="px-3 py-2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="space-y-3">
            <h2 class="text-lg font-semibold text-gray-900">Danh sách học viên hoàn thành</h2>
            {{ $this->table }}
        </div>
    </div>
</x-filament::page>
