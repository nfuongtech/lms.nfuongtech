<x-filament::page>
    <div class="space-y-6">
        @once
            <style>
                .fi-ta-filters-trigger {
                    display: none;
                }

                .fi-ta-filter-indicators > span:first-child {
                    display: none;
                }

                .fi-ta-filter-indicators::before {
                    content: 'Đang áp dụng lọc';
                    margin-right: 0.5rem;
                    font-size: 0.75rem;
                    font-weight: 600;
                    text-transform: uppercase;
                    color: rgb(55 65 81);
                }
            </style>
        @endonce

        @php($pageHeading = trim($this->getHeading() ?? $this->getTitle() ?? ''))

        @if($pageHeading !== '')
            <h1 class="text-2xl font-semibold text-gray-900">{{ $pageHeading }}</h1>
        @endif

        @php($selectedCourse = $this->filterState['course_id'] ?? null)
        @php($totals = $this->summaryTotals)

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4 border-b">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                    <div class="space-y-1">
                        <h2 class="text-base font-semibold text-gray-900">Bộ lọc</h2>
                        <p class="text-xs text-gray-500">Chọn các điều kiện để hiển thị danh sách khóa học và học viên tương ứng.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                        <x-filament::button wire:click="exportFilteredExcel" color="primary" icon="heroicon-o-arrow-down-tray">
                            Xuất Excel
                        </x-filament::button>
                    </div>
                </div>
            </div>

            <div class="px-4 py-4">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div class="space-y-1">
                        <label for="filter-year" class="text-sm font-medium text-gray-700">Năm</label>
                        <select
                            id="filter-year"
                            wire:model.live="filterYear"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        >
                            @foreach($this->yearOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="filter-month" class="text-sm font-medium text-gray-700">Tháng</label>
                        <select
                            id="filter-month"
                            wire:model.live="filterMonth"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        >
                            <option value="">Tất cả</option>
                            @foreach($this->monthOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="filter-week" class="text-sm font-medium text-gray-700">Tuần</label>
                        <select
                            id="filter-week"
                            wire:model.live="filterWeek"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        >
                            <option value="">Tất cả</option>
                            @foreach($this->weekOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="filter-from-date" class="text-sm font-medium text-gray-700">Từ ngày</label>
                        <input
                            id="filter-from-date"
                            type="date"
                            wire:model.live="filterFromDate"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        />
                    </div>

                    <div class="space-y-1">
                        <label for="filter-to-date" class="text-sm font-medium text-gray-700">Đến ngày</label>
                        <input
                            id="filter-to-date"
                            type="date"
                            wire:model.live="filterToDate"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        />
                    </div>

                    <div class="space-y-1">
                        <label for="filter-training-types" class="text-sm font-medium text-gray-700">Loại hình đào tạo</label>
                        <select
                            id="filter-training-types"
                            wire:model.live="filterTrainingTypes"
                            multiple
                            size="4"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        >
                            @foreach($this->trainingTypeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500">Giữ Ctrl (Windows) hoặc Command (macOS) để chọn nhiều lựa chọn.</p>
                    </div>

                    <div class="space-y-1 md:col-span-2 xl:col-span-3">
                        <label for="filter-course" class="text-sm font-medium text-gray-700">Khóa học</label>
                        <select
                            id="filter-course"
                            wire:model.live="filterCourseId"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        >
                            <option value="">Tất cả khóa học</option>
                            @forelse($this->courseOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                                <option value="" disabled>Không có khóa học phù hợp</option>
                            @endforelse
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4 border-b">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                    <div class="space-y-1">
                        <h2 class="text-base font-semibold text-gray-900">Tổng quan khóa học</h2>
                        <p class="text-xs text-gray-500">Nhấn vào hàng trong bảng bên dưới để xem chi tiết danh sách học viên hoàn thành theo từng khóa học.</p>
                    </div>

                </div>
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
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $this->statusBadgeClass($row['trang_thai'] ?? null) }} whitespace-nowrap">
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
