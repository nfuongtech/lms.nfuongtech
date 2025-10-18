{{-- resources/views/filament/widgets/enrollment-overview-chart.blade.php --}}
<x-filament::widget>
    <x-filament::card>
        <x-slot name="heading">
            <div class="flex flex-col gap-1">
                <span class="text-lg font-semibold text-slate-800">Tình trạng học viên theo tháng</span>
                <span class="text-sm text-slate-500">So sánh số lượng đăng ký, hoàn thành và không hoàn thành theo kế hoạch đào tạo</span>
            </div>
        </x-slot>

        <div class="space-y-6">
            <div class="flex flex-wrap gap-4">
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
                    <span>Tháng</span>
                    <select
                        wire:model.live="month"
                        class="mt-1 w-40 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-normal text-slate-700 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                    >
                        <option value="">Tất cả</option>
                        @foreach($monthOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div
                class="relative h-80"
                x-data="dashboardChart({
                    type: 'bar',
                    data: @entangle('chartData').live,
                    options: @entangle('chartOptions').live,
                })"
            >
                <canvas x-ref="canvas" class="rounded-xl bg-gradient-to-br from-slate-50 to-white"></canvas>
            </div>

            @if(!empty($this->month) && !empty($monthSummary['label']))
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-blue-500">Đăng ký</p>
                        <p class="mt-2 text-3xl font-semibold text-blue-600">{{ $monthSummary['dang_ky'] }}</p>
                        <p class="mt-1 text-xs text-blue-500/80">{{ $monthSummary['label'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-emerald-500">Hoàn thành</p>
                        <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ $monthSummary['hoan_thanh'] }}</p>
                        <p class="mt-1 text-xs text-emerald-500/80">Tổng học viên hoàn thành</p>
                    </div>

                    <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-rose-500">Không hoàn thành</p>
                        <p class="mt-2 text-3xl font-semibold text-rose-600">{{ $monthSummary['khong_hoan_thanh'] }}</p>
                        <div class="mt-3 space-y-1 text-sm text-rose-500">
                            <p class="flex items-center justify-between">
                                <span>Vắng phép</span>
                                <span class="font-semibold">{{ $monthSummary['vang_phep'] }}</span>
                            </p>
                            <p class="flex items-center justify-between">
                                <span>Vắng không phép</span>
                                <span class="font-semibold">{{ $monthSummary['vang_khong_phep'] }}</span>
                            </p>
                            @if(($monthSummary['khac'] ?? 0) > 0)
                                <p class="flex items-center justify-between text-rose-400">
                                    <span>Khác</span>
                                    <span class="font-semibold">{{ $monthSummary['khac'] }}</span>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif(empty($monthOptions))
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                    Chưa có dữ liệu thống kê cho năm đã chọn.
                </div>
            @endif
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
                            options: this.chartOptions,
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
                        this.chartInstance.options = this.chartOptions;
                        this.chartInstance.update('active');
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
                            const alpha = stops.length === 4 ? parseFloat(stops[3]) : 0.8;
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
