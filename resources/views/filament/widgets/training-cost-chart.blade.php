{{-- resources/views/filament/widgets/training-cost-chart.blade.php --}}
<x-filament::widget>
    <x-filament::card>
        <x-slot name="heading">
            <div class="flex flex-col gap-1">
                <span class="text-lg font-semibold text-slate-800">Chi phí đào tạo</span>
                <span class="text-sm text-slate-500">Tổng hợp chi phí dựa trên học viên hoàn thành theo loại hình đào tạo</span>
            </div>
        </x-slot>

        <div class="space-y-6">
            <div class="flex flex-wrap items-end gap-4">
                <label class="flex flex-col text-sm font-medium text-slate-600">
                    <span>Năm</span>
                    <select
                        wire:model.live="year"
                        class="mt-1 w-40 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-normal text-slate-700 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                    >
                        @foreach($yearOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="flex flex-col text-sm font-medium text-slate-600">
                    <span>Loại hình đào tạo</span>
                    <select
                        wire:model.live="selectedTrainingTypes"
                        multiple
                        size="4"
                        class="mt-1 min-h-[9rem] w-64 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-normal text-slate-700 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                    >
                        @foreach($trainingTypeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="mt-1 text-xs font-normal text-slate-400">Giữ Ctrl hoặc Cmd để chọn nhiều loại hình</span>
                </label>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-amber-500">Tổng chi phí</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-600">{{ number_format($totalCost, 0, ',', '.') }}<span class="text-sm font-medium text-amber-500"> VND</span></p>
                    <p class="mt-1 text-xs text-amber-500/80">Theo năm {{ $year ?? ($yearOptions ? array_key_first($yearOptions) : now()->format('Y')) }}</p>
                </div>

                <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4 shadow-sm md:col-span-2">
                    <p class="text-xs font-semibold uppercase text-indigo-500">Chi phí theo loại hình</p>
                    @if(!empty($typeTotals))
                        <dl class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($typeTotals as $type => $value)
                                <div class="rounded-xl border border-indigo-100 bg-white/60 px-3 py-2 text-sm text-indigo-600 shadow-sm">
                                    <dt class="font-medium">{{ $type }}</dt>
                                    <dd class="text-base font-semibold text-indigo-700">{{ number_format($value, 0, ',', '.') }} <span class="text-xs font-normal text-indigo-400">VND</span></dd>
                                </div>
                            @endforeach
                        </dl>
                    @else
                        <p class="mt-2 text-sm text-indigo-400">Chưa có dữ liệu chi phí cho bộ lọc hiện tại.</p>
                    @endif
                </div>
            </div>

            <div
                class="relative h-80"
                x-data="dashboardChart({
                    type: 'bar',
                    data: @entangle('chartData').live,
                    options: @entangle('chartOptions').live,
                })"
            >
                <canvas x-ref="canvas" class="rounded-xl bg-gradient-to-br from-white to-slate-50"></canvas>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                if (window.__dashboardChartRegistered) {
                    return;
                }

                window.__dashboardChartRegistered = true;

                Alpine.data('dashboardChart', ({ type = 'bar', data, options }) => ({
                        chartInstance: null,
                        type,
                        chartData: data,
                        chartOptions: options,
                        init() {
                            this.$watch('chartData', () => this.refresh());
                            this.$watch('chartOptions', () => this.refresh());
                            this.render();
                        },
                        render() {
                            const ctx = this.$refs.canvas.getContext('2d');
                            const preparedData = this.prepareData(ctx);

                            const config = {
                                type: this.type,
                                data: preparedData,
                                options: this.prepareOptions(),
                            };

                            if (this.chartInstance) {
                                this.chartInstance.destroy();
                            }

                            this.chartInstance = new Chart(ctx, config);
                        },
                        refresh() {
                            if (! this.chartInstance) {
                                this.render();
                                return;
                            }

                            const ctx = this.$refs.canvas.getContext('2d');
                            const preparedData = this.prepareData(ctx);
                            this.chartInstance.data = preparedData;
                            this.chartInstance.options = this.prepareOptions();
                            this.chartInstance.update('active');
                        },
                        cloneOptions() {
                            if (typeof structuredClone === 'function') {
                                return structuredClone(this.chartOptions ?? {});
                            }

                            return JSON.parse(JSON.stringify(this.chartOptions ?? {}));
                        },
                        prepareOptions() {
                            const cloned = this.cloneOptions();
                            const meta = cloned.__meta ?? {};

                            if (cloned.__meta) {
                                delete cloned.__meta;
                            }

                            cloned.plugins = cloned.plugins || {};
                            cloned.plugins.tooltip = cloned.plugins.tooltip || {};
                            cloned.plugins.tooltip.callbacks = {
                                label(context) {
                                    const datasetLabel = context.dataset?.label ?? '';
                                    const value = context.parsed?.y ?? 0;
                                    const locale = meta.tooltipLocale ?? 'vi-VN';
                                    const suffix = meta.tooltipSuffix ?? '';
                                    const formatted = new Intl.NumberFormat(locale).format(value);

                                    return `${datasetLabel}: ${formatted}${suffix}`.trim();
                                },
                            };

                            cloned.scales = cloned.scales || {};
                            cloned.scales.y = cloned.scales?.y || {};
                            cloned.scales.y.ticks = cloned.scales.y.ticks || {};

                            const divisor = meta.tickDivisor ?? 1000000;
                            const tickLocale = meta.tickLocale ?? 'vi-VN';
                            const tickSuffix = meta.tickSuffix ?? '';

                            cloned.scales.y.ticks.callback = (value) => {
                                const normalized = divisor ? value / divisor : value;
                                const formatted = new Intl.NumberFormat(tickLocale).format(normalized);

                                return `${formatted}${tickSuffix}`.trim();
                            };

                            return cloned;
                        },
                        prepareData(ctx) {
                            if (! this.chartData?.datasets) {
                                return this.chartData;
                            }

                            const gradient = (color) => {
                                const match = /rgba?\(([^)]+)\)/.exec(color);
                                if (! match) {
                                    return color;
                                }

                                const stops = match[1].split(',').map(part => part.trim());
                                const alpha = stops.length === 4 ? parseFloat(stops[3]) : 0.85;
                                const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
                                gradient.addColorStop(0, `rgba(${stops[0]}, ${stops[1]}, ${stops[2]}, ${alpha})`);
                                gradient.addColorStop(1, `rgba(${stops[0]}, ${stops[1]}, ${stops[2]}, ${Math.max(alpha - 0.55, 0.1)})`);
                                return gradient;
                            };

                            const datasets = this.chartData.datasets.map(dataset => ({
                                ...dataset,
                                backgroundColor: Array.isArray(dataset.backgroundColor)
                                    ? dataset.backgroundColor.map(color => gradient(color))
                                    : gradient(dataset.backgroundColor),
                            }));

                            return {
                                ...this.chartData,
                                datasets,
                            };
                        },
                    }));
            });
        </script>
    @endpush
@endonce
