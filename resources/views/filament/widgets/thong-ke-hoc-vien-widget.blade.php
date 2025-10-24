@include('filament.widgets.partials.dashboard-chart-script')

@once
    @push('styles')
        <style>
            .tkhv-table {
                border-collapse: collapse;
                border-spacing: 0;
                table-layout: fixed;
                width: 100%;
                background-color: #caeefb;
            }

            .tkhv-table th,
            .tkhv-table td {
                font-size: clamp(0.45rem, 0.4rem + 0.14vw, 0.62rem);
                line-height: 1.25;
                padding: 0.35rem 0.4rem;
                white-space: nowrap;
            }

            .tkhv-table th {
                font-weight: 600;
            }

            .tkhv-table thead th,
            .tkhv-table tfoot th,
            .tkhv-table tfoot td,
            .tkhv-table tbody td {
                background-color: #caeefb;
                border-color: rgba(15, 76, 117, 0.18);
            }

            .tkhv-table .tkhv-sticky {
                position: sticky;
                left: 0;
                z-index: 25;
                box-shadow: 4px 0 8px -6px rgba(15, 23, 42, 0.25);
                background-color: #caeefb;
            }

            .dark .tkhv-table {
                background-color: rgba(202, 238, 251, 0.12);
            }

            .dark .tkhv-table th,
            .dark .tkhv-table td,
            .dark .tkhv-table .tkhv-sticky {
                background-color: rgba(202, 238, 251, 0.2);
                border-color: rgba(202, 238, 251, 0.25);
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
    $isAllMonths = $this->month === null || $this->month === '' || $this->month === 'all';
    $studentSummaryHeading = $isAllMonths
        ? 'Cả năm'
        : collect($displayMonths)->map(fn ($m) => 'Tháng ' . str_pad($m, 2, '0', STR_PAD_LEFT))->implode(', ');
    $chartMinWidth = max(320, count($months) * 56);
@endphp

<x-filament::widget>
    <x-filament::card class="p-6 space-y-6">
        <div class="space-y-1">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Thống kê Học viên</h2>
            <p class="text-sm text-slate-500 dark:text-gray-400">
                Năm {{ $this->year ?? '—' }} • Thống kê theo loại hình đào tạo. (ĐK: Đăng ký, HT: Hoàn thành, KHT: Không hoàn thành)
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800/60">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-end gap-3">
                        <label class="flex w-full flex-col text-sm font-medium text-slate-700 dark:text-slate-200 sm:w-auto sm:flex-1">
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
                        <label class="flex w-full flex-col text-sm font-medium text-slate-700 dark:text-slate-200 sm:w-auto sm:flex-1">
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
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Tổng số học viên</p>
                <p class="mb-4 text-sm font-semibold text-emerald-700 dark:text-emerald-200">{{ $studentSummaryHeading }}</p>
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
                    <p>Phạm vi thời gian: <span class="font-semibold">{{ $isAllMonths ? 'Cả năm' : $activeMonthLabel }}</span></p>
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
                <div class="w-full">
                    <table class="tkhv-table divide-y divide-gray-200 text-slate-700 dark:divide-gray-700 dark:text-slate-200">
                        <thead class="bg-transparent">
                            <tr>
                                <th scope="col" class="tkhv-sticky px-2 py-2 text-left font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">
                                    Loại hình đào tạo
                                </th>
                                @foreach ($months as $month)
                                    <th scope="col" colspan="3" class="px-1.5 py-2 text-center font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300 border-l border-gray-200 dark:border-gray-700">
                                        {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}
                                    </th>
                                @endforeach
                                <th scope="col" colspan="3" class="px-2 py-2 text-center font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300 border-l border-gray-200 dark:border-gray-700">
                                    Tổng năm
                                </th>
                            </tr>
                            <tr>
                                <th class="tkhv-sticky px-2 py-1.5 text-left font-medium text-slate-500 dark:text-slate-400"></th>
                                @foreach ($months as $month)
                                    <th class="px-1.5 py-1.5 text-center font-medium text-slate-500 dark:text-slate-400 border-l border-gray-200 dark:border-gray-700">ĐK</th>
                                    <th class="px-1.5 py-1.5 text-center font-medium text-slate-500 dark:text-slate-400">HT</th>
                                    <th class="px-1.5 py-1.5 text-center font-medium text-slate-500 dark:text-slate-400">KHT</th>
                                @endforeach
                                <th class="px-2 py-1.5 text-center font-semibold text-slate-600 dark:text-slate-300 border-l border-gray-200 dark:border-gray-700">ĐK</th>
                                <th class="px-2 py-1.5 text-center font-semibold text-slate-600 dark:text-slate-300">HT</th>
                                <th class="px-2 py-1.5 text-center font-semibold text-slate-600 dark:text-slate-300">KHT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($rows as $row)
                                <tr class="hover:bg-[#b8e4f5] dark:hover:bg-slate-700/60">
                                    <td class="tkhv-sticky z-10 px-2 py-1.5 font-medium text-slate-800 dark:text-slate-100 whitespace-nowrap">
                                        {{ $row['label'] }}
                                    </td>
                                    @foreach ($months as $month)
                                        @php
                                            $bucket = $row['monthly'][$month] ?? ['dk' => 0, 'ht' => 0, 'kht' => 0];
                                        @endphp
                                        <td class="px-1.5 py-1.5 text-center text-slate-600 dark:text-slate-300 border-l border-gray-200 dark:border-gray-700">
                                            {{ $bucket['dk'] > 0 ? number_format($bucket['dk']) : '—' }}
                                        </td>
                                        <td class="px-1.5 py-1.5 text-center font-semibold text-slate-900 dark:text-white">
                                            {{ $bucket['ht'] > 0 ? number_format($bucket['ht']) : '—' }}
                                        </td>
                                        <td class="px-1.5 py-1.5 text-center text-slate-600 dark:text-slate-300">
                                            {{ $bucket['kht'] > 0 ? number_format($bucket['kht']) : '—' }}
                                        </td>
                                    @endforeach
                                    <td class="px-2 py-1.5 text-center font-semibold text-slate-700 dark:text-slate-200 border-l border-gray-200 dark:border-gray-700">
                                        {{ ($row['total']['dk'] ?? 0) > 0 ? number_format($row['total']['dk']) : '—' }}
                                    </td>
                                    <td class="px-2 py-1.5 text-center font-bold text-slate-900 dark:text-white">
                                        {{ ($row['total']['ht'] ?? 0) > 0 ? number_format($row['total']['ht']) : '—' }}
                                    </td>
                                    <td class="px-2 py-1.5 text-center font-semibold text-slate-700 dark:text-slate-200">
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
                        <tfoot class="bg-transparent">
                            <tr>
                                <th class="tkhv-sticky tkhv-sticky-footer px-2 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">Cộng</th>
                                @foreach ($months as $month)
                                    @php
                                        $bucket = $perMonth[$month] ?? ['dk' => 0, 'ht' => 0, 'kht' => 0];
                                    @endphp
                                    <th class="px-1.5 py-2 text-center font-semibold text-slate-700 dark:text-slate-200 border-l border-gray-200 dark:border-gray-700">
                                        {{ $bucket['dk'] > 0 ? number_format($bucket['dk']) : '—' }}
                                    </th>
                                    <th class="px-1.5 py-2 text-center font-semibold text-slate-700 dark:text-slate-200">
                                        {{ $bucket['ht'] > 0 ? number_format($bucket['ht']) : '—' }}
                                    </th>
                                    <th class="px-1.5 py-2 text-center font-semibold text-slate-700 dark:text-slate-200">
                                        {{ $bucket['kht'] > 0 ? number_format($bucket['kht']) : '—' }}
                                    </th>
                                @endforeach
                                <th class="px-2 py-2 text-center font-semibold text-slate-800 dark:text-white border-l border-gray-200 dark:border-gray-700">
                                    {{ ($totals['dk'] ?? 0) > 0 ? number_format($totals['dk']) : '—' }}
                                </th>
                                <th class="px-2 py-2 text-center font-semibold text-slate-800 dark:text-white">
                                    {{ ($totals['ht'] ?? 0) > 0 ? number_format($totals['ht']) : '—' }}
                                </th>
                                <th class="px-2 py-2 text-center font-semibold text-slate-800 dark:text-white">
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
                <div class="relative h-[180px] w-full overflow-x-auto rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:overflow-x-hidden" wire:ignore>
                    <div class="min-h-full" style="min-width: {{ $chartMinWidth }}px;">
                        <canvas id="{{ $chartId }}" class="!h-full w-full"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
