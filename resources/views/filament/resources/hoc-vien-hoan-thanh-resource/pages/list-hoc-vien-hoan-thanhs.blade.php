<x-filament::page>
    <div class="space-y-6">
        @once
            <style>
                /* Thẻ “Đang áp dụng lọc” */
                .fi-ta-filter-indicators > span:first-child { display: none; }
                .fi-ta-filter-indicators::before {
                    content: 'Đang áp dụng lọc';
                    margin-right: .5rem;
                    font-size: .75rem; font-weight: 600; text-transform: uppercase;
                    color: rgb(55 65 81);
                }

                /* Ẩn NÚT Filters trong header (mọi biến thể) */
                .fi-ta-header .fi-ta-filters-trigger,
                .fi-ta-header [data-fi-action="open-filters"],
                .fi-ta-header [data-fi-action="toggle-filters"],
                .fi-ta-header [data-fi-action*="filter" i],
                .fi-ta-header [aria-label*="filter" i],
                .fi-ta-header [title*="filter" i],
                .fi-ta-header [aria-label*="lọc" i],
                .fi-ta-header [title*="lọc" i],
                .fi-ta-header .fi-icon-btn-icon,
                .fi-ta-header .fi-badge {
                    display: none !important;
                }

                /* Ẩn LUÔN cả PANEL Filters (layout AboveContent) */
                .fi-ta .fi-ta-filters,
                .fi-ta [data-fi-panel="filters"],
                .fi-ta [data-fi-filters-panel],
                .fi-ta [data-fi-panel-id="filters"],
                .fi-ta .fi-section:has(> .fi-section-header h3),
                .fi-ta .fi-section:has(> .fi-section-header .fi-section-header-heading) {
                    /* dùng JS bên dưới để kiểm tra tiêu đề, CSS này chỉ hỗ trợ when matched by JS */
                }

                /* Hàng filter 5 ô luôn cùng 1 hàng, trượt ngang khi hẹp */
                .filters-inline-row{
                    display: flex;
                    flex-wrap: nowrap;
                    align-items: flex-end;
                    gap: .75rem;
                    overflow-x: auto;
                    padding-bottom: .25rem;
                }
                .filters-inline-row > label{
                    flex: 0 0 auto;
                    min-width: 150px;
                }
            </style>
        @endonce

        {{-- JS xoá triệt để:
             - Nút Filters (kể cả chỉ-icon + badge)
             - Cả panel Filters "Above content"
        --}}
        <script>
            function hideFilamentFilterButtons(){
                document.querySelectorAll('.fi-ta-header button, .fi-ta-header a, .fi-ta-header [role="button"]').forEach(el => {
                    const text = (el.textContent || '').trim().toLowerCase();
                    const aria = (el.getAttribute('aria-label') || '').trim().toLowerCase();
                    const title = (el.getAttribute('title') || '').trim().toLowerCase();
                    const dataAction = (el.getAttribute('data-fi-action') || '').trim().toLowerCase();
                    const html = (el.innerHTML || '').toLowerCase();

                    const isFilterAction =
                        ['filters','filter','chọn lọc','lọc'].some(k => text.includes(k)) ||
                        ['filters','filter','chọn lọc','lọc'].some(k => aria.includes(k)) ||
                        ['filters','filter'].some(k => title.includes(k)) ||
                        dataAction.includes('filter') || dataAction.includes('filters') ||
                        html.includes('funnel') || html.includes('filter');

                    if (isFilterAction) {
                        const prev = el.previousElementSibling;
                        if (prev && prev.classList.contains('fi-badge')) prev.remove();
                        const wrapper = el.closest('[class*="filters" i]') || el.closest('.fi-ta-filters');
                        if (wrapper) wrapper.remove(); else el.remove();
                    }
                });
            }

            function removeAboveContentFiltersPanel(){
                // Xoá section/panel có heading "Filters" hoặc "Bộ lọc"
                document.querySelectorAll('.fi-ta .fi-section, .fi-ta .fi-ta-filters, .fi-ta [data-fi-panel], .fi-ta [data-fi-filters-panel]').forEach(el => {
                    const headingEl = el.querySelector('h3, .fi-section-header-heading, .fi-section-header h3');
                    const heading = (headingEl?.textContent || '').trim().toLowerCase();
                    if (heading === 'filters' || heading === 'bộ lọc') {
                        el.remove();
                    }
                });
            }

            const events = [
                'DOMContentLoaded','livewire:navigated','livewire:load','livewire:update',
                'alpine:init','alpine:initialized','turbo:load','htmx:afterSettle'
            ];
            events.forEach(evt => document.addEventListener(evt, () => {
                requestAnimationFrame(() => {
                    hideFilamentFilterButtons();
                    removeAboveContentFiltersPanel();
                });
            }));
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

        {{-- =================== BỘ LỌC (tuỳ chỉnh riêng) =================== --}}
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

                    {{-- HÀNG 5 Ô LUÔN CÙNG 1 HÀNG --}}
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
            <div class="mt-3">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament::page>
