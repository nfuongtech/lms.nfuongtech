<x-filament::page>
    <div class="space-y-6">
        @once
            <style>
                /* Thẻ “Đang áp dụng lọc” (nếu Filament còn render) */
                .fi-ta-filter-indicators > span:first-child { display: none; }
                .fi-ta-filter-indicators::before {
                    content: 'Đang áp dụng lọc';
                    margin-right: .5rem;
                    font-size: .75rem; font-weight: 600; text-transform: uppercase;
                    color: rgb(55 65 81);
                }

                /* Ẩn triệt để Filters CHỈ trong vùng hvht-table (giữ Search & Columns) */
                #hvht-table .fi-ta-header [x-ref="filtersTrigger"],
                #hvht-table .fi-ta-header [data-fi-action*="filter" i],
                #hvht-table .fi-ta-header [aria-label*="filter" i],
                #hvht-table .fi-ta-header [title*="filter" i],
                #hvht-table .fi-ta-header .fi-badge,
                #hvht-table .fi-ta-filter-indicators,
                #hvht-table .fi-ta-filters,
                #hvht-table [data-fi-panel="filters"],
                #hvht-table [data-fi-filters-panel],
                #hvht-table [data-fi-panel-id="filters"],
                #hvht-table .fi-ta-filters-above-content-ctn {
                    display: none !important;
                }

                /* ====== BỐ CỤC HÀNG LỌC RESPONSIVE ======
                   - Mobile: wrap, không trượt ngang
                   - >=1024px (PC): cố định 1 hàng, chia đều
                */
                .filters-inline-row{
                    display: flex;
                    flex-wrap: wrap;          /* mobile: wrap để không cần trượt ngang */
                    align-items: end;
                    gap: .75rem;
                }
                .filters-inline-row > label{
                    flex: 1 1 160px;          /* co giãn đều, min 160px */
                    min-width: 140px;
                }
                @media (min-width: 1024px){
                    .filters-inline-row{
                        flex-wrap: nowrap;    /* PC: 1 hàng cố định */
                    }
                    .filters-inline-row > label{
                        flex: 1 1 0;          /* chia đều không vỡ layout */
                        min-width: 0;
                    }
                }
            </style>
        @endonce

        {{-- JS: dọn sạch Filters & header container trong hvht-table, bền vững qua re-render --}}
        <script>
            (function(){
                function removeHvhtFilters() {
                    const root = document.getElementById('hvht-table');
                    if (!root) return;

                    const tableRoot = root.querySelector('.fi-ta') || root;

                    // Badge + button Filters
                    tableRoot.querySelectorAll('.fi-ta-header .fi-badge').forEach(badge => {
                        const next = badge.nextElementSibling;
                        if (next && (next.tagName === 'BUTTON' || next.getAttribute('role') === 'button')) next.remove();
                        badge.remove();
                    });
                    tableRoot.querySelectorAll('.fi-ta-header [x-ref="filtersTrigger"], .fi-ta-header [data-fi-action*="filter"], .fi-ta-header [aria-label*="filter" i], .fi-ta-header [title*="filter" i]')
                        .forEach(el => el.remove());

                    // Indicators + panels
                    tableRoot.querySelectorAll('.fi-ta-filter-indicators, .fi-ta-filters, [data-fi-panel="filters"], [data-fi-filters-panel], [data-fi-panel-id="filters"]')
                        .forEach(el => el.remove());

                    // Section "Filters"/"Bộ lọc"
                    tableRoot.querySelectorAll('.fi-section').forEach(sec => {
                        const h = (sec.querySelector('.fi-section-header-heading, .fi-section-header h3, h3')?.textContent || '').trim().toLowerCase();
                        if (h === 'filters' || h === 'bộ lọc') sec.remove();
                    });

                    // Header container Filters của Filament
                    tableRoot.querySelectorAll('.fi-ta-filters-above-content-ctn').forEach(el => el.remove());
                }

                function initHvhtFilterHider() {
                    const root = document.getElementById('hvht-table');
                    if (!root) return;

                    removeHvhtFilters();

                    if (!root.__hvhtObserver) {
                        root.__hvhtObserver = new MutationObserver(removeHvhtFilters);
                        root.__hvhtObserver.observe(root, { childList: true, subtree: true });
                    }
                }

                ['DOMContentLoaded','livewire:load','livewire:update','livewire:navigated','alpine:init','alpine:initialized']
                    .forEach(evt => document.addEventListener(evt, () => requestAnimationFrame(initHvhtFilterHider)));
            })();
        </script>

        @php($filterData = data_get($this->tableFilters, 'bo_loc.data', []))
        @php($selectedCourseId = $this->selectedCourseId)
        @php($selectedTrainingTypes = collect($filterData['training_types'] ?? [])->filter(fn($v) => $v !== null && $v !== '')->map(fn($v) => (string) $v)->values()->all())
        @php($courseOptions = $this->courseFilterOptions)
        @php($totals = $this->summaryTotals)

        {{-- =================== KHỐI TỔNG QUAN + NÚT LỆNH =================== --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4 border-b">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="space-y-1">
                        <h2 class="text-base font-semibold text-gray-900">Tổng quan khóa học</h2>
                        <p class="text-xs text-gray-500">
                            Nhấn vào hàng trong bảng để chọn/bỏ chọn khóa học. Bảng "Danh sách học viên hoàn thành" sẽ tự lọc theo khóa đang chọn.
                        </p>
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
                            @php($isSelected = $selectedCourseId === (int) ($row['id'] ?? 0))
                            <tr
                                wire:key="summary-{{ $row['id'] }}"
                                wire:click="selectCourseFromSummary({{ $row['id'] }})"
                                class="cursor-pointer transition border-l-4 {{ $isSelected ? 'border-primary-500 bg-primary-50 shadow-inner' : 'border-transparent bg-white hover:bg-primary-50' }}"
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
                        @php($totals = $this->summaryTotals)
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

        {{-- =================== BỘ LỌC (tuỳ chỉnh) =================== --}}
        @php($years  = $this->availableYears)
        @php($months = $this->availableMonths)
        @php($weeks  = $this->availableWeeks)
        @php($trainingOptions = $this->getTrainingTypeOptions())

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4 border-b">
                <h2 class="text-base font-semibold text-gray-900">Bộ lọc</h2>
                <p class="text-xs text-gray-500">Tùy chỉnh phạm vi thời gian, loại hình đào tạo và khóa học để xem danh sách học viên.</p>
            </div>

            <div class="p-4">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm space-y-5">
                    <div class="filters-inline-row">
                        <label class="flex flex-col gap-1.5 text-sm font-medium text-slate-700">
                            <span>Năm</span>
                            <select
                                wire:model.live="tableFilters.bo_loc.data.year"
                                wire:change="handleYearChange($event.target.value)"
                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                @forelse($years as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @empty
                                    <option value="{{ now()->year }}">{{ now()->year }}</option>
                                @endforelse
                            </select>
                        </label>

                        <label class="flex flex-col gap-1.5 text-sm font-medium text-slate-700">
                            <span>Tháng</span>
                            <select
                                wire:model.live="tableFilters.bo_loc.data.month"
                                wire:change="handleMonthChange($event.target.value)"
                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                @foreach($months as $m)
                                    <option value="{{ $m }}">{{ sprintf('%02d', $m) }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="flex flex-col gap-1.5 text-sm font-medium text-slate-700">
                            <span>Tuần</span>
                            <select
                                wire:model.live="tableFilters.bo_loc.data.week"
                                wire:change="handleWeekChange($event.target.value)"
                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">Tất cả</option>
                                @foreach($weeks as $w)
                                    <option value="{{ $w }}">{{ $w }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="flex flex-col gap-1.5 text-sm font-medium text-slate-700">
                            <span>Từ ngày</span>
                            <input
                                type="date"
                                wire:model.lazy="tableFilters.bo_loc.data.from_date"
                                wire:change="handleFromDateChange($event.target.value)"
                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </label>

                        <label class="flex flex-col gap-1.5 text-sm font-medium text-slate-700">
                            <span>Đến ngày</span>
                            <input
                                type="date"
                                wire:model.lazy="tableFilters.bo_loc.data.to_date"
                                wire:change="handleToDateChange($event.target.value)"
                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </label>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2.5">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <span class="text-sm font-medium text-slate-700">Loại hình đào tạo</span>
                                <div class="flex items-center gap-3">
                                    <button
                                        type="button"
                                        wire:click="selectAllTrainingTypes"
                                        wire:loading.attr="disabled"
                                        class="text-xs font-semibold text-primary-600 transition hover:text-primary-700"
                                    >
                                        Chọn tất cả
                                    </button>

                                    @if(!empty($selectedTrainingTypes))
                                        <button
                                            type="button"
                                            wire:click="clearTrainingTypeFilters"
                                            wire:loading.attr="disabled"
                                            class="text-xs font-semibold text-primary-600 transition hover:text-primary-700"
                                        >
                                            Bỏ chọn
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @forelse($trainingOptions as $value => $label)
                                    @php($isSelectedType = in_array((string) $value, $selectedTrainingTypes, true))
                                    <button
                                        type="button"
                                        wire:key="training-type-{{ md5($value) }}"
                                        wire:click="toggleTrainingType({{ \Illuminate\Support\Js::from($value) }})"
                                        wire:loading.attr="disabled"
                                        @class([
                                            'rounded-full border px-3 py-1.5 text-xs font-medium tracking-wide transition focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1',
                                            'border-primary-500 bg-primary-500 text-white shadow-sm' => $isSelectedType,
                                            'border-slate-300 bg-white text-slate-700 hover:border-primary-400 hover:bg-primary-50' => ! $isSelectedType,
                                        ])
                                    >
                                        {{ $label }}
                                    </button>
                                @empty
                                    <p class="text-xs text-slate-400">Chưa có dữ liệu loại hình đào tạo.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700">Khóa học</label>
                            <select
                                wire:model.live="tableFilters.bo_loc.data.course_id"
                                wire:change="handleCourseChange($event.target.value)"
                                class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">Tất cả khóa học</option>
                                @forelse($courseOptions as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @empty
                                    <option value="" disabled>Không có khóa học phù hợp</option>
                                @endforelse
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4">
            <h2 class="text-lg font-semibold text-gray-900">Danh sách học viên hoàn thành</h2>
            <div class="mt-3" id="hvht-table">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament::page>
