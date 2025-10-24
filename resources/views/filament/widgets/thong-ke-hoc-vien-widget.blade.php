@php
    /** @var \App\Filament\Widgets\ThongKeHocVienWidget $this */
    $chartId = 'thongKeHocVienChart_' . $this->getId();

    $yearOptions = $this->yearOptions;
    $trainingTypeOptions = $this->trainingTypeOptions;
    $monthlySummaryTableData = $this->monthlySummaryTableData;
    $selectedTrainingTypeLabels = collect($this->selectedTrainingTypes)
        ->map(fn ($value) => $trainingTypeOptions[$value] ?? $value)
        ->filter()
        ->values()
        ->all();
    $selectedTrainingTypeSummary = empty($selectedTrainingTypeLabels)
        ? 'Tất cả loại hình đào tạo'
        : implode(', ', $selectedTrainingTypeLabels);
    $totals = ['dk' => 0, 'ht' => 0, 'kht' => 0];
    foreach ($monthlySummaryTableData as $row) {
        $totals['dk'] += $row['dk'] ?? 0;
        $totals['ht'] += $row['ht'] ?? 0;
        $totals['kht'] += $row['kht'] ?? 0;
    }
@endphp

<x-filament::widget>
    <x-filament::card class="p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Thống kê Học viên</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Năm {{ $this->year ?? '—' }} · Loại hình: {{ $selectedTrainingTypeSummary }}.
                Số liệu theo tháng (ĐK: Đăng ký, HT: Hoàn thành, KHT: Không hoàn thành).
            </p>
        </div>

        <div
            x-data="{
                chart: null,
                retry: 0,
                data: @entangle('chartPayload').live,
                opts: @entangle('chartOptionsPayload').live,
                init() { this.$nextTick(() => this.render()); },
                render() {
                    const el = document.getElementById(@js($chartId));
                    if (!el) return;

                    if (typeof window.Chart === 'undefined') {
                        if (this.retry < 10) {
                            this.retry += 1;
                            setTimeout(() => this.render(), 160);
                        }
                        return;
                    }

                    // tooltip hiển thị số
                    const options = JSON.parse(JSON.stringify(this.opts || {}));
                    options.plugins ??= {};
                    options.plugins.tooltip ??= {};
                    options.plugins.tooltip.callbacks ??= {};
                    options.plugins.tooltip.callbacks.label = function (ctx) {
                        const v = ctx.parsed?.y ?? 0;
                        const name = ctx.dataset?.label ?? '';
                        const s = (v || 0).toLocaleString('vi-VN');
                        return name ? `${name}: ${s}` : s;
                    };

                    if (this.chart) { try { this.chart.destroy(); } catch(e) {} }
                    this.retry = 0;
                    this.chart = new Chart(el.getContext('2d'), { type: 'bar', data: this.data, options });
                }
            }"
            x-init="init()"
            x-effect="render()"
        >
            {{-- Bộ lọc + Tổng quan nhanh --}}
            <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <div class="space-y-4">
                        <label class="flex flex-col text-sm font-medium text-slate-700 dark:text-slate-200">
                            <span class="mb-1.5">Năm</span>
                            <select
                                wire:model.live="year"
                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                            >
                                @foreach ($yearOptions as $y => $label)
                                    <option value="{{ $y }}" @selected($y == ($this->year ?? null))>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Loại hình đào tạo</span>
                                @if (!empty($trainingTypeOptions))
                                    <div class="flex items-center gap-3">
                                        <button
                                            type="button"
                                            wire:click="selectAllTrainingTypes"
                                            wire:loading.attr="disabled"
                                            class="text-xs font-semibold text-primary-600 transition hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                                        >
                                            Chọn tất cả
                                        </button>
                                        @if (!empty($this->selectedTrainingTypes))
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
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @forelse ($trainingTypeOptions as $value => $label)
                                    @php $isSelected = in_array($value, $this->selectedTrainingTypes, true); @endphp
                                    <button
                                        type="button"
                                        wire:key="training-type-{{ md5($value) }}"
                                        wire:click="toggleTrainingType(@js($value))"
                                        wire:loading.attr="disabled"
                                        @class([
                                            'rounded-full border px-3 py-1.5 text-xs font-medium tracking-wide transition focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1',
                                            'border-primary-500 bg-primary-500 text-white' => $isSelected,
                                            'border-slate-300 bg-white text-slate-700 hover:border-primary-400 hover:bg-primary-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:hover:border-primary-400' => ! $isSelected,
                                        ])
                                    >
                                        {{ $label }}
                                    </button>
                                @empty
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Chưa có dữ liệu loại hình đào tạo.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 lg:col-span-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-800/50 dark:bg-blue-900/40">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-300">Tổng Đăng ký</p>
                        <p class="mt-2 text-3xl font-bold text-blue-700 dark:text-blue-100">
                            {{ $totals['dk'] > 0 ? number_format($totals['dk']) : '0' }}
                        </p>
                    </div>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-800/50 dark:bg-emerald-900/40">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Tổng Hoàn thành</p>
                        <p class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-100">
                            {{ $totals['ht'] > 0 ? number_format($totals['ht']) : '0' }}
                        </p>
                    </div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-800/50 dark:bg-amber-900/40">
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-300">Tổng Không hoàn thành</p>
                        <p class="mt-2 text-3xl font-bold text-amber-700 dark:text-amber-100">
                            {{ $totals['kht'] > 0 ? number_format($totals['kht']) : '0' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Bảng tháng --}}
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Bảng số liệu chi tiết theo tháng</h3>
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="hv-monthly-table min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th scope="col" class="sticky left-0 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 z-10">
                                    Loại hình
                                </th>
                                @foreach (range(1, 12) as $m)
                                    <th scope="col" colspan="3" class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 border-l border-gray-200 dark:border-gray-700">
                                        T{{ sprintf('%02d', $m) }}
                                    </th>
                                @endforeach
                                <th scope="col" colspan="3" class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 border-l border-gray-200 dark:border-gray-700 font-semibold">
                                    Tổng Năm
                                </th>
                            </tr>
                            <tr>
                                <th scope="col" class="sticky left-0 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 z-10"></th>
                                @foreach (range(1, 12) as $m)
                                    <th class="px-1 py-2 text-center text-xs font-medium tracking-wider text-gray-500 dark:text-gray-400 border-l border-gray-200 dark:border-gray-700">ĐK</th>
                                    <th class="px-1 py-2 text-center text-xs font-medium tracking-wider text-gray-500 dark:text-gray-400">HT</th>
                                    <th class="px-1 py-2 text-center text-xs font-medium tracking-wider text-gray-500 dark:text-gray-400">KHT</th>
                                @endforeach
                                <th class="px-1 py-2 text-center text-xs font-medium tracking-wider text-gray-500 dark:text-gray-400 border-l border-gray-200 dark:border-gray-700 font-semibold">ĐK</th>
                                <th class="px-1 py-2 text-center text-xs font-medium tracking-wider text-gray-500 dark:text-gray-400 font-semibold">HT</th>
                                <th class="px-1 py-2 text-center text-xs font-medium tracking-wider text-gray-500 dark:text-gray-400 font-semibold">KHT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            <tr>
                                <td class="sticky left-0 bg-white dark:bg-gray-900 px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white z-10">
                                    Cộng
                                </td>
                                @foreach (range(1, 12) as $m)
                                    @php $d = $monthlySummaryTableData[$m] ?? ['dk'=>0,'ht'=>0,'kht'=>0]; @endphp
                                    <td class="px-1 py-2 whitespace-nowrap text-center text-sm text-gray-600 dark:text-gray-300 border-l border-gray-200 dark:border-gray-700">
                                        {{ $d['dk'] > 0 ? number_format($d['dk']) : '-' }}
                                    </td>
                                    <td class="px-1 py-2 whitespace-nowrap text-center text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $d['ht'] > 0 ? number_format($d['ht']) : '-' }}
                                    </td>
                                    <td class="px-1 py-2 whitespace-nowrap text-center text-sm text-gray-600 dark:text-gray-300">
                                        {{ $d['kht'] > 0 ? number_format($d['kht']) : '-' }}
                                    </td>
                                @endforeach
                                <td class="px-1 py-2 whitespace-nowrap text-center text-sm font-semibold text-gray-700 dark:text-gray-200 border-l border-gray-200 dark:border-gray-700">
                                    {{ $totals['dk'] > 0 ? number_format($totals['dk']) : '-' }}
                                </td>
                                <td class="px-1 py-2 whitespace-nowrap text-center text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $totals['ht'] > 0 ? number_format($totals['ht']) : '-' }}
                                </td>
                                <td class="px-1 py-2 whitespace-nowrap text-center text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    {{ $totals['kht'] > 0 ? number_format($totals['kht']) : '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Biểu đồ --}}
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Biểu đồ tổng quan theo tháng</h3>
                <div class="relative h-[420px] rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800" wire:ignore>
                    <canvas id="{{ $chartId }}" wire:ignore></canvas>
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>

@push('styles')
    <style>
        .hv-monthly-table {
            font-size: clamp(0.68rem, 0.82vw, 0.85rem);
        }
        .hv-monthly-table thead th {
            font-size: clamp(0.58rem, 0.74vw, 0.78rem);
            letter-spacing: 0.04em;
        }
        .hv-monthly-table tbody td {
            font-size: clamp(0.68rem, 0.84vw, 0.9rem);
        }
    </style>
@endpush

@include('filament.widgets.partials.dashboard-chart-script')
