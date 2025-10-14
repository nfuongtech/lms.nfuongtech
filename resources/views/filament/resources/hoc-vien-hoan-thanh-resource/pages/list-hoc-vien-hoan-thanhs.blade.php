<x-filament::page>
    <div class="space-y-6">
        @once
            <style>
                .fi-ta-header .fi-ta-search {
                    display: none !important;
                }

                .fi-ta-header .fi-ta-filters,
                .fi-ta-filter-indicators {
                    display: none !important;
                }
            </style>
        @endonce

        @php($pageHeading = trim($this->getHeading() ?? $this->getTitle() ?? ''))
        @php($headerActions = method_exists($this, 'getCachedHeaderActions') ? $this->getCachedHeaderActions() : [])

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            @if($pageHeading !== '')
                <div class="text-2xl font-semibold text-gray-900">{{ $pageHeading }}</div>
            @endif

            <div class="flex w-full flex-col gap-3 md:w-auto md:flex-row md:items-center md:justify-end">
                <div class="relative w-full md:w-72 lg:w-80">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" />
                        </svg>
                    </span>
                    <input
                        type="text"
                        wire:model.debounce.500ms="tableSearch"
                        placeholder="Tìm kiếm học viên..."
                        class="fi-input block w-full rounded-lg border-gray-300 pl-9 pr-3 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                </div>

                @if(! empty($headerActions))
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        @foreach($headerActions as $action)
                            {{ $action }}
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="border-b px-4 py-4">
                <h2 class="text-base font-semibold text-gray-900">Chọn lọc thông tin</h2>
            </div>
            <div class="px-4 py-4">
                <div class="flex flex-wrap gap-4">
                    <div class="w-full sm:w-32">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Năm</label>
                        <select
                            wire:model.live="filterYear"
                            class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            @foreach($this->yearOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-32">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Tháng</label>
                        <select
                            wire:model.live="filterMonth"
                            class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">Tất cả</option>
                            @foreach($this->monthOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-32">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Tuần</label>
                        <select
                            wire:model.live="filterWeek"
                            class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">Tất cả</option>
                            @foreach($this->weekOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-40 md:w-44 lg:w-52">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Từ ngày</label>
                        <input
                            type="date"
                            wire:model.live="filterFromDate"
                            class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                    </div>

                    <div class="w-full sm:w-40 md:w-44 lg:w-52">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Đến ngày</label>
                        <input
                            type="date"
                            wire:model.live="filterToDate"
                            class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                    </div>

                    <div class="w-full min-w-[12rem] lg:flex-1">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Khóa học</label>
                        <select
                            wire:model.live="filterCourseId"
                            class="fi-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">Tất cả khóa học</option>
                            @foreach($this->courseOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full min-w-[12rem] lg:flex-1">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Loại hình đào tạo</label>
                        <select
                            wire:model.live="filterTrainingTypes"
                            multiple
                            size="3"
                            class="fi-input h-auto w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            @foreach($this->trainingTypeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Giữ Ctrl (Windows) hoặc Command (Mac) để chọn nhiều mục.</p>
                    </div>
                </div>
            </div>
        </div>

        @php($selectedCourse = $this->filterState['course_id'] ?? null)
        @php($totals = $this->summaryTotals)

        <div class="bg-white rounded-lg shadow">
            <div class="border-b px-4 py-4">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Tổng quan khóa học</h2>
                    <p class="text-xs text-gray-500">Nhấn vào hàng trong bảng để xem danh sách học viên hoàn thành theo khóa học.</p>
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
                                    <span class="inline-flex items-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium {{ $this->statusBadgeClass($row['trang_thai'] ?? null) }}">
                                        {{ $row['trang_thai'] ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center text-gray-700">{{ $row['tong_gio'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $row['giang_vien'] }}</td>
                                <td class="px-3 py-2 whitespace-pre-line text-gray-700">{{ $row['thoi_gian'] }}</td>
                                <td class="px-3 py-2 text-center text-gray-700">{{ number_format($row['so_luong_hv'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center font-semibold text-emerald-600">{{ number_format($row['hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center font-semibold text-rose-600">{{ number_format($row['khong_hoan_thanh'], 0, ',', '.') }}</td>
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
