@php
    /** @var \App\Filament\Widgets\ThongKeHocVienWidget $this */
    $chartId = 'thongKeHocVienChart_' . $this->getId();

    $yearOptions = $this->yearOptions;
    $trainingTypeOptions = $this->trainingTypeOptions;
    $monthlySummaryTableData = $this->monthlySummaryTableData;
@endphp

<x-filament::widget>
    <x-filament::card class="p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Thống kê Học viên</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Năm {{ $this->year ?? '—' }}. Số liệu theo tháng (ĐK: Đăng ký, HT: Hoàn thành, K-HT: Không hoàn thành).
            </p>
        </div>

        <div
            x-data="{
                chart: null,
                data: @entangle('chartPayload').live,
                opts: @entangle('chartOptionsPayload').live,
                init() { this.$nextTick(() => this.render()); },
                render() {
                    const el = document.getElementById(@js($chartId));
                    if (!el || typeof Chart === 'undefined') return;

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
                    this.chart = new Chart(el.getContext('2d'), { type: 'bar', data: this.data, options });
                }
            }"
            x-init="init()"
            x-effect="render()"
        >
            {{-- Bộ lọc --}}
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 space-y-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Năm --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Năm</label>
                        <select
                            wire:model.live="year"
                            class="fi-input block w-full rounded-lg border-none py-1.5 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-300 transition duration-75 focus:ring-2 focus:ring-primary-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:focus:ring-primary-500"
                        >
                            @foreach ($yearOptions as $y => $label)
                                <option value="{{ $y }}" @selected($y == ($this->year ?? null))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Loại hình đào tạo (chiếm 2 cột) --}}
                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Loại hình đào tạo</span>
                            @if (!empty($trainingTypeOptions))
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                        wire:click="selectAllTrainingTypes"
                                        wire:loading.attr="disabled"
                                        class="text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400">
                                        Chọn tất cả
                                    </button>
                                    <button type="button"
                                        wire:click="clearTrainingTypeFilters"
                                        wire:loading.attr="disabled"
                                        x-show="$wire.selectedTrainingTypes.length > 0"
                                        style="display: none;"
                                        class="text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400">
                                        Bỏ chọn
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Chip giống UI “Học viên hoàn thành” --}}
                        <div class="flex flex-wrap gap-2">
                            @forelse ($trainingTypeOptions as $value => $label)
                                @php
                                    $isSelected = in_array($value, $this->selectedTrainingTypes, true);
                                    $btn = $isSelected
                                        ? 'fi-badge fi-color-primary bg-primary-600/10 text-primary-700 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30'
                                        : 'fi-badge fi-color-gray bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20 hover:bg-gray-100 dark:hover:bg-gray-400/15';
                                @endphp
                                <button
                                    type="button"
                                    wire:key="training-type-{{ md5($value) }}"
                                    wire:click="toggleTrainingType(@js($value))"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-0.5 text-xs font-medium transition-colors {{ $btn }}"
                                >
                                    {{ $label }}
                                </button>
                            @empty
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Không tìm thấy loại hình nào. Hệ thống sẽ thử lấy từ Đăng ký hoặc Quy tắc mã khi có dữ liệu.
                                </span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bảng tháng --}}
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Bảng số liệu chi tiết theo tháng</h3>
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th scope="col" class="sticky left-0 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 z-10">
                                    Trạng thái
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
                                    <th class="px-1 py-2 text-center text-xs font-medium tracking-wider text-gray-500 dark:text-gray-400">K-HT</th>
                                @endforeach
                                <th class="px-1 py-2 text-center text-xs font-medium tracking-wider text-gray-500 dark:text-gray-400 border-l border-gray-200 dark:border-gray-700 font-semibold">ĐK</th>
                                <th class="px-1 py-2 text-center text-xs font-medium tracking-wider text-gray-500 dark:text-gray-400 font-semibold">HT</th>
                                <th class="px-1 py-2 text-center text-xs font-medium tracking-wider text-gray-500 dark:text-gray-400 font-semibold">K-HT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            @php
                                $totals = ['dk' => 0, 'ht' => 0, 'kht' => 0];
                                foreach ($monthlySummaryTableData as $row) {
                                    $totals['dk'] += $row['dk'] ?? 0;
                                    $totals['ht'] += $row['ht'] ?? 0;
                                    $totals['kht'] += $row['kht'] ?? 0;
                                }
                            @endphp

                            <tr>
                                <td class="sticky left-0 bg-white dark:bg-gray-900 px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white z-10">
                                    Đăng ký / Hoàn thành / Không HT
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
                <div class="relative h-[420px] rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <canvas id="{{ $chartId }}"></canvas>
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
