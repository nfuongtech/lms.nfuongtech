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

                /* Ẩn nút Filters của bảng dưới (đa ngôn ngữ) */
                .fi-ta-header .fi-ta-filters-trigger,
                .fi-ta-header [data-fi-action="open-filters"],
                .fi-ta-header [dusk="filament.tables.filters.toggle-button"] {
                    display: none !important;
                }

                /* Token multi-select look */
                .token-input{ border:1px solid #e5e7eb;border-radius:.5rem;padding:.25rem .5rem;min-height:2.5rem;display:flex;flex-wrap:wrap;gap:.25rem;align-items:center;background:#fff;}
                .token-chip{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:.375rem;padding:.125rem .5rem;font-size:.75rem;display:flex;align-items:center;gap:.25rem}
                .token-chip button{line-height:1;border:none;background:transparent;cursor:pointer}
                .token-search{border:none;outline:none;min-width:10ch;flex:1 0 auto;font-size:.875rem;padding:.25rem}
                .token-dropdown{position:absolute;z-index:40;background:#fff;border:1px solid #e5e7eb;border-radius:.5rem;box-shadow:0 10px 15px rgba(0,0,0,.05);max-height:18rem;overflow:auto;margin-top:.25rem;width:100%;}
                .token-item{padding:.5rem .75rem;cursor:pointer}
                .token-item:hover{background:#f8fafc}
                .token-item.active{background:#eff6ff}
            </style>
        @endonce

        {{-- Thêm JS backup ẩn nút Filters nếu CSS trên không bắt được --}}
        <script>
            function hideFilamentFilterButtons(){
                document.querySelectorAll('.fi-ta-header button, .fi-ta-header a').forEach(el=>{
                    const t=(el.textContent||'').trim().toLowerCase();
                    if(['filters','filter','chọn lọc thông tin'].includes(t)) el.style.display='none';
                });
            }
            document.addEventListener('DOMContentLoaded', hideFilamentFilterButtons);
            document.addEventListener('livewire:navigated', hideFilamentFilterButtons);
            document.addEventListener('livewire:load', hideFilamentFilterButtons);
        </script>

        @php($selectedCourses = $this->selectedCourseIds ?? [])
        @php($totals = $this->summaryTotals)

        {{-- =================== KHỐI TỔNG QUAN + NÚT LỆNH =================== --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4 border-b">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="space-y-1">
                        <h2 class="text-base font-semibold text-gray-900">Tổng quan khóa học</h2>
                        <p class="text-xs text-gray-500">
                            Nhấn vào hàng trong bảng để chọn/bỏ chọn khóa học. Bảng "Danh sách học viên hoàn thành" sẽ tự lọc theo các khóa đã chọn.
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
                    @php($summaryRows = $this->summaryRows)
                    <tbody class="divide-y divide-gray-200">
                        @php($map = array_flip($selectedCourses ?? []))
                        @if($summaryRows->isNotEmpty())
                            @foreach($summaryRows as $row)
                                @php($isSelected = isset($map[$row['id']]))
                                <tr
                                    wire:key="summary-{{ $row['id'] }}"
                                    wire:click="selectCourseFromSummary({{ $row['id'] }})"
                                    class="cursor-pointer transition {{ $isSelected ? 'bg-primary-50' : 'bg-white hover:bg-primary-50' }}"
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
                            @endforeach
                        @else
                            <tr>
                                <td colspan="12" class="px-3 py-4 text-center text-sm text-gray-500">Chưa có khóa học phù hợp với bộ lọc.</td>
                            </tr>
                        @endif
                    </tbody>
                    @if($summaryRows->isNotEmpty())
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

        {{-- =================== BỘ LỌC NHANH (1 HÀNG) =================== --}}
        @php($years  = $this->availableYears)
        @php($months = $this->availableMonths)
        @php($weeks  = $this->availableWeeks)
        @php($trainingTypeOptions = $this->getTrainingTypeOptions())
        @php($selectedTrainingTypes = $this->selectedTrainingTypes ?? [])
        @php($courseOptions = $this->summaryCourseOptions)
        @php($selectedCourseIds = $this->selectedCourseIds ?? [])

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-3 border-b">
                {{-- Giữ 1 hàng, co giãn khi màn hình nhỏ --}}
                <div class="flex flex-row flex-wrap items-end gap-3">
                    {{-- Năm --}}
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600">Năm</label>
                        <select
                            class="fi-input fi-input-wrp-input rounded-lg border-gray-300 text-sm"
                            wire:model.defer="tableFilters.bo_loc.data.year"
                            wire:change="applyQuickFilters"
                        >
                            @forelse($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @empty
                                <option value="{{ now()->year }}">{{ now()->year }}</option>
                            @endforelse
                        </select>
                    </div>

                    {{-- Tháng --}}
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600">Tháng</label>
                        <select
                            class="fi-input fi-input-wrp-input rounded-lg border-gray-300 text-sm"
                            wire:model.defer="tableFilters.bo_loc.data.month"
                            wire:change="applyQuickFilters"
                        >
                            <option value="">--</option>
                            @foreach($months as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tuần --}}
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600">Tuần</label>
                        <select
                            class="fi-input fi-input-wrp-input rounded-lg border-gray-300 text-sm"
                            wire:model.defer="tableFilters.bo_loc.data.week"
                            wire:change="applyQuickFilters"
                        >
                            <option value="">--</option>
                            @foreach($weeks as $w)
                                <option value="{{ $w }}">{{ $w }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Từ ngày --}}
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600">Từ ngày</label>
                        <input type="date"
                            class="fi-input fi-input-wrp-input rounded-lg border-gray-300 text-sm"
                            wire:model.defer="tableFilters.bo_loc.data.from_date"
                            wire:change="applyQuickFilters" />
                    </div>

                    {{-- Đến ngày --}}
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600">Đến ngày</label>
                        <input type="date"
                            class="fi-input fi-input-wrp-input rounded-lg border-gray-300 text-sm"
                            wire:model.defer="tableFilters.bo_loc.data.to_date"
                            wire:change="applyQuickFilters" />
                    </div>

                </div>

                <div class="mt-3 flex flex-row flex-wrap gap-4 items-start">
                    {{-- Loại hình đào tạo --}}
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700">Loại hình đào tạo</span>
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
                        <div class="flex flex-wrap gap-2">
                            @if(!empty($trainingTypeOptions))
                                @foreach($trainingTypeOptions as $value => $label)
                                    @php $isSelected = in_array($value, $selectedTrainingTypes ?? [], true); @endphp
                                    <button
                                        type="button"
                                        wire:key="training-type-{{ md5($value) }}"
                                        wire:click="toggleTrainingType({{ \Illuminate\Support\Js::from($value) }})"
                                        wire:loading.attr="disabled"
                                        @class([
                                            'rounded-full border px-3 py-1.5 text-xs font-medium tracking-wide transition focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1',
                                            'border-primary-500 bg-primary-500 text-white' => $isSelected,
                                            'border-slate-300 bg-white text-slate-700 hover:border-primary-400 hover:bg-primary-50' => ! $isSelected,
                                        ])
                                    >
                                        {{ $label }}
                                    </button>
                                @endforeach
                            @else
                                <p class="text-xs text-slate-400">Chưa có dữ liệu loại hình đào tạo.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Khóa học --}}
                    <div class="flex-1 min-w-[18rem] space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700">Khóa học</span>
                            @if(!empty($selectedCourseIds))
                                <button
                                    type="button"
                                    wire:click="clearQuickCourseFilter"
                                    wire:loading.attr="disabled"
                                    class="text-xs font-semibold text-primary-600 transition hover:text-primary-700"
                                >
                                    Bỏ chọn
                                </button>
                            @endif
                        </div>

                        @if(!empty($courseOptions))
                            <select
                                multiple
                                wire:model.defer="selectedCourseIds"
                                wire:change="applyQuickFilters"
                                wire:loading.attr="disabled"
                                class="fi-input fi-input-wrp-input rounded-lg border-gray-300 text-sm min-h-[9rem]"
                            >
                                @foreach($courseOptions as $option)
                                    @php
                                        $label = trim(implode(' - ', array_filter([
                                            $option['code'] ?? null,
                                            $option['name'] ?? null,
                                        ])));
                                    @endphp
                                    <option value="{{ $option['id'] }}">
                                        {{ $label !== '' ? $label : ('Khóa #' . $option['id']) }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400">Giữ Ctrl (Windows) hoặc Cmd (macOS) để chọn nhiều khóa.</p>
                        @else
                            <p class="text-xs text-slate-400">Chưa có khóa học phù hợp để chọn.</p>
                        @endif
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
    </div>
</x-filament::page>
