@include('filament.widgets.partials.dashboard-chart-script')

@once
    @push('styles')
        <style>
            .tkhv-table th,
            .tkhv-table td {
                font-size: clamp(0.55rem, 0.5rem + 0.18vw, 0.75rem);
                }

            .tkhv-table th {
                font-weight: 600;
            }

            .tkhv-table .tkhv-sticky {
                position: sticky;
                left: 0;
                z-index: 25;
                box-shadow: 4px 0 8px -6px rgba(15, 23, 42, 0.35);
            }

            .dark .tkhv-table .tkhv-sticky {
                box-shadow: 4px 0 12px -7px rgba(15, 23, 42, 0.65);
            }

            .tkhv-table thead .tkhv-sticky,
            .tkhv-table tfoot .tkhv-sticky {
                background-color: #f1f5f9;
            }

            .tkhv-table tbody .tkhv-sticky {
                background-color: #ffffff;
            }

            .dark .tkhv-table thead .tkhv-sticky,
            .dark .tkhv-table tfoot .tkhv-sticky {
                background-color: rgba(30, 41, 59, 0.96);
            }

            .dark .tkhv-table tbody .tkhv-sticky {
                background-color: rgba(15, 23, 42, 0.95);
            }

            .tkhv-table .tkhv-sticky-footer {
                z-index: 20;
            }
        </style>
    @endpush
@endonce

@php
    /** @var \App\Filament\Widgets\ThongKeHocVienWidget $this */
    $chartId = 'thongKeHocVienChart_' . $this->getId();
    $yearOptions = $this->yearOptions;
    $trainingTypeOptions = $this->trainingTypeOptions;
    $tableData = $this->monthlySummaryTableData;
    $rows = $tableData['rows'] ?? [];
    $summary = $tableData['summary'] ?? ['perMonth' => [], 'total' => []];
    $perMonth = $summary['perMonth'] ?? [];
    $totals = $summary['total'] ?? ['dk' => 0, 'ht' => 0, 'kht' => 0];
    $displayTotals = $summary['displayTotal'] ?? $totals;
    $months = $tableData['months'] ?? range(1, 12);
    $displayMonths = $summary['displayMonths'] ?? $months;
    $hasData = $tableData['hasData'] ?? false;
    $selectedTypes = collect($this->selectedTrainingTypes ?? [])->filter()->values();
    $totalTypeCount = count($trainingTypeOptions);
    $activeTypeCount = $selectedTypes->isNotEmpty() ? $selectedTypes->count() : $totalTypeCount;
    $allSelected = $totalTypeCount > 0 && $activeTypeCount === $totalTypeCount;
    $completionRate = ($displayTotals['dk'] ?? 0) > 0
        ? round((($displayTotals['ht'] ?? 0) / max(1, $displayTotals['dk'])) * 100, 1)
        : 0;
    $monthOptions = $this->monthOptions;
    $activeMonthLabel = collect($months)->map(fn ($m) => 'Tháng ' . str_pad($m, 2, '0', STR_PAD_LEFT))->implode(', ');
    $displayMonthLabel = collect($displayMonths)->map(fn ($m) => 'Tháng ' . str_pad($m, 2, '0', STR_PAD_LEFT))->implode(', ');
    $showYearSummary = $this->month === 'all' || $this->month === null || $this->month === '';
    $chartMinWidth = max(360, count($months) * 64);
@endphp

<x-filament::widget>
    <x-filament::card class="p-6 space-y-6">
        <div class="space-y-1">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Thống kê Học viên</h2>
            <p class="text-sm text-slate-500 dark:text-gray-400">
                Năm {{ $this->year ?? '—' }} • Thống kê theo loại hình đào tạo. (Đăng ký, Hoàn thành, Không hoàn thành)
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800/60">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-end gap-3 sm:flex-nowrap">
                        <label class="flex flex-col text-sm font-medium text-slate-700 dark:text-slate-200 min-w-[120px]">
                            <span class="mb-1.5">Năm</span>
                            <select
                                wire:model.live="year"
                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            >
                                @foreach ($yearOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="flex flex-col text-sm font-medium text-slate-700 dark:text-slate-200 min-w-[120px]">
                            <span class="mb-1.5">Tháng</span>
                            <select
                                wire:model.live="month"
                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            >
                                @foreach ($monthOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Loại hình đào tạo</span>

                            <div class="flex items-center gap-2">
                                @if(!$allSelected && $totalTypeCount > 0)
                                    <button
                                        type="button"
                                        wire:click="selectAllTrainingTypes"
                                        wire:loading.attr="disabled"
                                        class="text-xs font-semibold text-primary-600 transition hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                                    >
                                        Chọn tất cả
                                    </button>
                                @endif

                                @if($selectedTypes->isNotEmpty())
                                    <button
                                        type="button"
                                        wire:click="clearTrainingTypeFilters"
                                        wire:loading.attr="disabled"
                                        class="text-xs font-semibold text-primary-600 transition hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                                    >
                                        Bỏ chọn
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @forelse ($trainingTypeOptions as $value => $label)
                                @php
                                    $isSelected = $selectedTypes->contains($value);
                                @endphp
                                <button
                                    type="button"
                                    wire:key="training-type-{{ md5($value) }}"
                                    wire:click="toggleTrainingType({{ \Illuminate\Support\Js::from($value) }})"
                                    wire:loading.attr="disabled"
                                    @class([
                                        'rounded-full border px-3 py-1.5 text-xs font-medium transition focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1',
                                        'border-primary-500 bg-primary-500 text-white dark:border-primary-400 dark:bg-primary-500/90' => $isSelected,
                                        'border-slate-300 bg-white text-slate-700 hover:border-primary-400 hover:bg-primary-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-primary-400 dark:hover:bg-slate-700' => ! $isSelected,
                                    ])
                                >
                                    {{ $label }}
                                </button>
                            @empty
                                <p class="text-xs text-slate-400 dark:text-slate-300">
                                    Chưa có dữ liệu loại hình đào tạo. Vui lòng cập nhật Kế hoạch đào tạo.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Tổng số học viên</p>
                <div class="mb-3 space-y-1 text-xs font-medium text-emerald-700 dark:text-emerald-200">
                    @if($showYearSummary)
                        <p>Tổng số học viên: Theo năm</p>
                    @endif
                    @if($displayMonthLabel !== '')
                        <p>Tổng số học viên: {{ $displayMonthLabel }}</p>
                    @endif
                </div>
                <dl class="space-y-2">
                    <div class="flex items-center justify-between">
                        <dt class="text-xs font-medium text-emerald-700 dark:text-emerald-200">Đăng ký</dt>
                        <dd class="text-xl font-semibold text-emerald-800 dark:text-emerald-100">{{ number_format($displayTotals['dk'] ?? 0) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-xs font-medium text-emerald-700 dark:text-emerald-200">Hoàn thành</dt>
                        <dd class="text-xl font-semibold text-emerald-800 dark:text-emerald-100">{{ number_format($displayTotals['ht'] ?? 0) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-xs font-medium text-emerald-700 dark:text-emerald-200">Không hoàn thành</dt>
                        <dd class="text-xl font-semibold text-emerald-800 dark:text-emerald-100">{{ number_format($displayTotals['kht'] ?? 0) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 shadow-sm dark:border-sky-500/40 dark:bg-sky-500/10">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-sky-600 dark:text-sky-300">Tóm tắt</p>
                <div class="space-y-2 text-sm text-sky-700 dark:text-sky-100">
                    <p>Số loại hình đang hiển thị: <span class="font-semibold">{{ $activeTypeCount }}</span> / {{ $totalTypeCount }}</p>
                    <p>Tỷ lệ hoàn thành: <span class="font-semibold">{{ $completionRate }}%</span></p>
                    <p>Phạm vi thời gian: <span class="font-semibold">{{ $this->month === 'all' || $this->month === null ? 'Cả năm' : $activeMonthLabel }}</span></p>
                    <p>
                        Bộ lọc hiện tại:
                        <span class="font-medium">
                            {{ $selectedTypes->isEmpty() ? 'Tất cả loại hình' : $selectedTypes->map(fn ($value) => $trainingTypeOptions[$value] ?? $value)->implode(', ') }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bảng số liệu chi tiết theo tháng</h3>

            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="max-w-full overflow-x-auto">
                    <table class="tkhv-table min-w-full divide-y divide-gray-200 text-slate-700 dark:divide-gray-700 dark:text-slate-200">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th scope="col" class="tkhv-sticky px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300 bg-gray-50 dark:bg-gray-800">
                                    Loại hình đào tạo
                                </th>
                                @foreach ($months as $month)
                                    <th scope="col" colspan="3" class="px-2 py-3 text-center font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300 border-l border-gray-200 dark:border-gray-700">
                                        {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}
                                    </th>
                                @endforeach
                                <th scope="col" colspan="3" class="px-3 py-3 text-center font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300 border-l border-gray-200 dark:border-gray-700">
                                    Tổng năm
                                </th>
                            </tr>
                            <tr>
                                <th class="tkhv-sticky px-4 py-2 text-left font-medium text-slate-500 dark:text-slate-400 bg-gray-50 dark:bg-gray-800"></th>
                                @foreach ($months as $month)
                                    <th class="px-2 py-2 text-center font-medium text-slate-500 dark:text-slate-400 border-l border-gray-200 dark:border-gray-700 whitespace-nowrap">Đăng ký</th>
                                    <th class="px-2 py-2 text-center font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">Hoàn thành</th>
                                    <th class="px-2 py-2 text-center font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">Không hoàn thành</th>
                                @endforeach
                                <th class="px-3 py-2 text-center font-semibold text-slate-600 dark:text-slate-300 border-l border-gray-200 dark:border-gray-700 whitespace-nowrap">Đăng ký</th>
                                <th class="px-3 py-2 text-center font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap">Hoàn thành</th>
                                <th class="px-3 py-2 text-center font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap">Không hoàn thành</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            @forelse ($rows as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
                                    <td class="tkhv-sticky z-10 px-4 py-2 font-medium text-slate-800 dark:text-slate-100 whitespace-nowrap">
                                        {{ $row['label'] }}
                                    </td>
                                    @foreach ($months as $month)
                                        @php
                                            $bucket = $row['monthly'][$month] ?? ['dk' => 0, 'ht' => 0, 'kht' => 0];
                                        @endphp
                                        <td class="px-2 py-2 text-center text-slate-600 dark:text-slate-300 border-l border-gray-200 dark:border-gray-700">
                                            {{ $bucket['dk'] > 0 ? number_format($bucket['dk']) : '—' }}
                                        </td>
                                        <td class="px-2 py-2 text-center font-semibold text-slate-900 dark:text-white">
                                            {{ $bucket['ht'] > 0 ? number_format($bucket['ht']) : '—' }}
                                        </td>
                                        <td class="px-2 py-2 text-center text-slate-600 dark:text-slate-300">
                                            {{ $bucket['kht'] > 0 ? number_format($bucket['kht']) : '—' }}
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-2 text-center font-semibold text-slate-700 dark:text-slate-200 border-l border-gray-200 dark:border-gray-700">
                                        {{ ($row['total']['dk'] ?? 0) > 0 ? number_format($row['total']['dk']) : '—' }}
                                    </td>
                                    <td class="px-3 py-2 text-center font-bold text-slate-900 dark:text-white">
                                        {{ ($row['total']['ht'] ?? 0) > 0 ? number_format($row['total']['ht']) : '—' }}
                                    </td>
                                    <td class="px-3 py-2 text-center font-semibold text-slate-700 dark:text-slate-200">
                                        {{ ($row['total']['kht'] ?? 0) > 0 ? number_format($row['total']['kht']) : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 1 + count($months) * 3 + 3 }}" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-300">
                                        Chưa có dữ liệu phù hợp với bộ lọc hiện tại.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="tkhv-sticky tkhv-sticky-footer px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">Cộng</th>
                                @foreach ($months as $month)
                                    @php
                                        $bucket = $perMonth[$month] ?? ['dk' => 0, 'ht' => 0, 'kht' => 0];
                                    @endphp
                                    <th class="px-2 py-3 text-center font-semibold text-slate-700 dark:text-slate-200 border-l border-gray-200 dark:border-gray-700">
                                        {{ $bucket['dk'] > 0 ? number_format($bucket['dk']) : '—' }}
                                    </th>
                                    <th class="px-2 py-3 text-center font-semibold text-slate-700 dark:text-slate-200">
                                        {{ $bucket['ht'] > 0 ? number_format($bucket['ht']) : '—' }}
                                    </th>
                                    <th class="px-2 py-3 text-center font-semibold text-slate-700 dark:text-slate-200">
                                        {{ $bucket['kht'] > 0 ? number_format($bucket['kht']) : '—' }}
                                    </th>
                                @endforeach
                                <th class="px-3 py-3 text-center font-semibold text-slate-800 dark:text-white border-l border-gray-200 dark:border-gray-700 whitespace-nowrap">
                                    {{ ($totals['dk'] ?? 0) > 0 ? number_format($totals['dk']) : '—' }}
                                </th>
                                <th class="px-3 py-3 text-center font-semibold text-slate-800 dark:text-white whitespace-nowrap">
                                    {{ ($totals['ht'] ?? 0) > 0 ? number_format($totals['ht']) : '—' }}
                                </th>
                                <th class="px-3 py-3 text-center font-semibold text-slate-800 dark:text-white whitespace-nowrap">
                                    {{ ($totals['kht'] ?? 0) > 0 ? number_format($totals['kht']) : '—' }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Biểu đồ tổng quan theo tháng</h3>
            <div
                x-data="{
                    chart: null,
                    data: @entangle('chartPayload').live,
                    opts: @entangle('chartOptionsPayload').live,
                    render() {
                        const canvas = document.getElementById(@js($chartId));
                        if (!canvas) {
                            return;
                        }

                        if (typeof window.Chart === 'undefined') {
                            setTimeout(() => this.render(), 180);
                            return;
                        }

                        const Chart = window.Chart;
                        const ctx = canvas.getContext('2d');

                        if (this.chart) {
                            try { this.chart.destroy(); } catch (error) {}
                        }

                        const options = JSON.parse(JSON.stringify(this.opts || {}));
                        const data = JSON.parse(JSON.stringify(this.data || {}));
                        options.plugins ??= {};
                        options.plugins.tooltip ??= {};
                        options.plugins.tooltip.callbacks ??= {};
                        options.plugins.tooltip.callbacks.label = function (context) {
                            const value = context.parsed?.y ?? 0;
                            const label = context.dataset?.label ?? '';
                            const formatted = (value || 0).toLocaleString('vi-VN');
                            return label ? `${label}: ${formatted}` : formatted;
                        };

                        this.chart = new Chart(ctx, { type: 'bar', data, options });
                    }
                }"
                x-init="render()"
                x-effect="render()"
            >
                <div class="relative h-[260px] w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:h-[280px]" wire:ignore>
                    <div class="h-full w-full overflow-x-auto px-5 py-4">
                        <div class="min-h-full" style="min-width: {{ $chartMinWidth }}px;">
                        <canvas id="{{ $chartId }}" class="!h-full w-full"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
