<x-filament::page>
    <div class="space-y-6">
        @once
            <style>
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
                <div class="space-y-1">
                    <h2 class="text-base font-semibold text-gray-900">Bộ lọc dữ liệu</h2>
                    <p class="text-xs text-gray-500">Chọn thông tin bên dưới để lọc danh sách khóa học và học viên.</p>
                </div>
            </div>

            <div class="px-4 py-4 space-y-4">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700">Năm</label>
                        <select
                            wire:model.live="filterYear"
                            class="fi-input block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            @foreach($this->yearOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700">Tháng</label>
                        <select
                            wire:model.live="filterMonth"
                            class="fi-input block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">Tất cả</option>
                            @foreach($this->monthOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700">Tuần</label>
                        <select
                            wire:model.live="filterWeek"
                            class="fi-input block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">Tất cả</option>
                            @foreach($this->weekOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700">Từ ngày</label>
                        <input
                            type="date"
                            wire:model.live="filterFromDate"
                            class="fi-input block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700">Đến ngày</label>
                        <input
                            type="date"
                            wire:model.live="filterToDate"
                            class="fi-input block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700">Khóa học</label>
                        <select
                            wire:model.live="filterCourseId"
                            class="fi-input block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">Tất cả khóa học</option>
                            @foreach($this->courseOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1 xl:col-span-2">
                        <label class="text-sm font-medium text-gray-700">Loại hình đào tạo</label>
                        <select
                            multiple
                            wire:model.live="filterTrainingTypes"
                            class="fi-input block w-full min-h-[2.875rem] rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            @foreach($this->trainingTypeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500">Giữ Ctrl (Windows) hoặc Command (Mac) để chọn nhiều giá trị.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <x-filament::button
                        color="success"
                        icon="heroicon-o-arrow-down-tray"
                        wire:click="exportCurrentView"
                        wire:loading.attr="disabled"
                        wire:target="exportCurrentView"
                    >
                        Xuất Excel
                    </x-filament::button>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4 border-b">
                <div class="space-y-1">
                    <h2 class="text-base font-semibold text-gray-900">Tổng quan khóa học</h2>
                    <p class="text-xs text-gray-500">Nhấn vào hàng trong bảng bên dưới để xem chi tiết danh sách học viên hoàn thành theo từng khóa học.</p>
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
