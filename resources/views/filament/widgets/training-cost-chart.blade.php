{{-- resources/views/filament/widgets/training-cost-chart.blade.php --}}
<x-filament::widget>
    <x-filament::card class="p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-800">Thống kê Chi phí đào tạo</h2>
            <p class="mt-1 text-sm text-slate-500">Theo dõi chi phí theo loại hình và khoảng thời gian được chọn.</p>
        </div>

        @php
            $resolvedYear = $year ?? ($yearOptions ? array_key_first($yearOptions) : (int) now()->format('Y'));
            $periodLabel = $month ? ('Tháng ' . sprintf('%02d/%d', $month, $resolvedYear)) : 'Năm ' . $resolvedYear;
        @endphp

        {{-- Lưới 3 cột gọn --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            {{-- Cột 1: Bộ lọc --}}
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex flex-col text-sm font-medium text-slate-700">
                            <span class="mb-1.5">Năm</span>
                            <select
                                wire:model.live="year"
                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                @foreach($yearOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="flex flex-col text-sm font-medium text-slate-700">
                            <span class="mb-1.5">Tháng</span>
                            <select
                                wire:model.live="month"
                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">Tất cả</option>
                                @foreach($monthOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

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
                            @forelse($trainingTypeOptions as $value => $label)
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
                            @empty
                                <p class="text-xs text-slate-400">Chưa có dữ liệu loại hình đào tạo.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cột 2: Chi phí theo loại hình --}}
            <div class="rounded-lg border border-indigo-200 bg-white p-4 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-indigo-600">Chi phí theo loại hình</p>

                @if(!empty($typeTotals))
                    <ul class="rounded-md border border-indigo-100 bg-indigo-50/40 divide-y divide-indigo-100">
                        @foreach($typeTotals as $type => $value)
                            <li class="flex items-center justify-between gap-4 px-3 py-2">
                                <span class="text-xs font-medium text-indigo-700">{{ $type }}</span>
                                <span class="text-lg font-bold text-indigo-900">
                                    {{ number_format($value, 0, ',', '.') }}
                                    <span class="text-xs font-normal text-indigo-500">VND</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-indigo-400">Chưa có dữ liệu chi phí cho bộ lọc hiện tại.</p>
                @endif
            </div>

            {{-- Cột 3: Tổng chi phí --}}
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-amber-600">Tổng chi phí</p>
                <p class="text-4xl font-bold text-amber-700" style="font-size: calc(1.5rem + 2pt);">
                    {{ number_format($totalCost, 0, ',', '.') }}
                    <span class="text-base font-medium text-amber-500">VND</span>
                </p>
                <p class="mt-2 text-sm text-amber-600/80">{{ $periodLabel }}</p>
            </div>
        </div>

        {{-- Biểu đồ chi phí --}}
        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-700">Biểu đồ chi phí</h3>
            <div class="relative h-[380px] w-full">
                <canvas 
                    id="training-cost-chart-canvas"
                    wire:ignore
                    x-data="trainingCostChart"
                    x-init="initChart(
                        $el,
                        @js($chartData),
                        @js($chartOptions)
                    )"
                    @refresh-chart.window="updateChart(
                        @js($chartData),
                        @js($chartOptions)
                    )"
                ></canvas>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('trainingCostChart', () => ({
        chartInstance: null,
        
        initChart(canvas, data, options) {
            if (!canvas || typeof Chart === 'undefined') {
                console.error('Canvas or Chart.js not available');
                return;
            }
            if (this.chartInstance) this.chartInstance.destroy();

            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.error('Cannot get canvas context');
                return;
            }
            
            this.chartInstance = new Chart(ctx, {
                type: 'bar',
                data: data || { labels: [], datasets: [] },
                options: this.normalizeOptions(options || {})
            });
        },
        
        updateChart(data, options) {
            if (!this.chartInstance) return;
            this.chartInstance.data = data || { labels: [], datasets: [] };
            this.chartInstance.options = this.normalizeOptions(options || {});
            this.chartInstance.update();
        },
        
        // Thu hẹp khe hở giữa các cột, giữ font & layout như cũ
        normalizeOptions(opts) {
            const base = {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 6, right: 8, bottom: 6, left: 8 } },
                datasets: {
                    bar: {
                        categoryPercentage: 0.95,  // ~kín nhóm
                        barPercentage: 0.98,       // cột phủ gần hết nhóm
                        maxBarThickness: 44,
                        borderRadius: 3,
                        borderSkipped: false,
                        borderWidth: 0,            // bỏ viền để nhìn “đầy” hơn
                    },
                },
                scales: {
                    x: {
                        offset: false,             // bỏ đệm hai đầu trục
                        grid: { display: false },
                        ticks: { maxRotation: 0, autoSkip: true },
                        stacked: false,
                    },
                    y: {
                        beginAtZero: true,
                        grid: { drawBorder: false, color: 'rgba(0,0,0,0.06)' },
                        ticks: { precision: 0 },
                        stacked: false,
                    },
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { enabled: true, mode: 'index', intersect: false },
                },
            };

            const o = opts || {};
            const oScales = o.scales || {};
            const oPlugins = o.plugins || {};
            const oDatasets = o.datasets || {};

            const merged = Object.assign({}, base, o);

            const mergedScales = Object.assign({}, base.scales || {}, oScales);
            mergedScales.x = Object.assign({}, (base.scales && base.scales.x) || {}, oScales.x || {});
            mergedScales.y = Object.assign({}, (base.scales && base.scales.y) || {}, oScales.y || {});
            merged.scales = mergedScales;

            merged.plugins = Object.assign({}, base.plugins || {}, oPlugins);

            const mergedDatasets = Object.assign({}, base.datasets || {}, oDatasets);
            mergedDatasets.bar = Object.assign({}, (base.datasets && base.datasets.bar) || {}, oDatasets.bar || {});
            merged.datasets = mergedDatasets;

            return merged;
        }
    }));
});

document.addEventListener('livewire:initialized', () => {
    Livewire.hook('message.processed', () => {
        window.dispatchEvent(new CustomEvent('refresh-chart'));
    });
});
</script>
@endpush
