{{-- resources/views/filament/widgets/training-cost-chart.blade.php --}}
@include('filament.widgets.partials.dashboard-chart-script')

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
        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm" wire:ignore>
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-700">Biểu đồ chi phí</h3>
            <div style="position: relative; height: 190px; width: 100%;">
                <canvas id="chiPhiChart_{{ $this->getId() }}"></canvas>
            </div>
        </div>

        {{-- Bảng thống kê --}}
        <div class="mt-6">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Bảng thống kê chi phí đào tạo</h3>
                @if(!empty($tableData['labels']))
                    <span class="text-xs text-slate-500">
                        @if(count($tableData['labels']) > 1)
                            Theo tháng
                        @else
                            {{ $tableData['labels'][0] }}
                        @endif
                    </span>
                @endif
            </div>

            @if($tableData['hasData'] ?? false)
                @if(count($tableData['labels'] ?? []) > 1)
                    <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
                        <table class="min-w-[640px] divide-y divide-slate-200 text-sm">
                            <thead style="background-color: #FFFCD5;">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Loại hình</th>
                                    @foreach($tableData['labels'] as $label)
                                        <th class="px-3 py-3 text-center font-semibold text-slate-600">{{ $label }}</th>
                                    @endforeach
                                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Tổng</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($tableData['rows'] as $row)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ $row['label'] }}</td>
                                        @foreach($row['values'] as $value)
                                            <td class="px-3 py-3 text-center text-slate-700">
                                                {{ number_format((float) $value, 0, ',', '.') }}
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                            {{ number_format((float) $row['total'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">Tổng theo tháng</th>
                                    @foreach($tableData['columnTotals'] as $total)
                                        <th class="px-3 py-3 text-center font-semibold text-slate-700">
                                            {{ number_format((float) $total, 0, ',', '.') }}
                                        </th>
                                    @endforeach
                                    <th class="px-4 py-3 text-right font-semibold text-slate-800">
                                        {{ number_format((float) $tableData['grandTotal'], 0, ',', '.') }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="mt-3 overflow-hidden rounded-lg border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead style="background-color: #FFFCD5;">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Loại hình</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Chi phí (VND)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($tableData['rows'] as $row)
                                    <tr class="hover:bg-slate-50 text-slate-700">
                                        <td class="px-4 py-3 font-medium">{{ $row['label'] }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                            {{ number_format((float) ($row['values'][0] ?? 0), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">Tổng chi phí</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-800">
                                        {{ number_format((float) ($tableData['columnTotals'][0] ?? 0), 0, ',', '.') }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            @else
                <p class="mt-3 rounded-lg border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500">
                    Chưa có dữ liệu chi phí cho bộ lọc hiện tại.
                </p>
            @endif
        </div>
    </x-filament::card>
</x-filament::widget>

@push('scripts')
    <script>
        (function () {
            const widgetId = @json($this->getId());
            const canvasId = `chiPhiChart_${widgetId}`;
            let chartInstance = null;

            const chartData = @json($chartData);
            const chartOptions = @json($chartOptions);

            const createChart = () => {
                const canvas = document.getElementById(canvasId);
                if (!canvas || typeof window.Chart === 'undefined') {
                    return false;
                }

                const ctx = canvas.getContext('2d');

                if (chartInstance) {
                    chartInstance.destroy();
                }

                chartInstance = new window.Chart(ctx, {
                    type: 'bar',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        ...chartOptions,
                        plugins: {
                            ...(chartOptions.plugins || {}),
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    boxWidth: 12,
                                    color: '#1e293b',
                                    ...(chartOptions.plugins?.legend?.labels || {}),
                                },
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                mode: 'index',
                                intersect: false,
                                ...(chartOptions.plugins?.tooltip || {}),
                            },
                            barValueLabels: {
                                ...(chartOptions.plugins?.barValueLabels || {}),
                            },
                        },
                    },
                });

                return true;
            };

            const ensureChart = (retries = 10) => {
                if (createChart()) {
                    return;
                }

                if (retries <= 0) {
                    return;
                }

                setTimeout(() => ensureChart(retries - 1), 180);
            };

            const init = () => ensureChart();

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init, { once: true });
            } else {
                setTimeout(init, 60);
            }

            const reinit = () => ensureChart();

            if (typeof document !== 'undefined') {
                document.addEventListener('livewire:initialized', () => {
                    if (window.Livewire?.hook) {
                        window.Livewire.hook('morph.updated', ({ component }) => {
                            if (component.id === widgetId) {
                                setTimeout(reinit, 80);
                            }
                        });
                    }
                });

                document.addEventListener('livewire:navigated', () => {
                    setTimeout(reinit, 80);
                });
            }
        })();
    </script>
@endpush
