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
            $__id = $this->getId();
            $canvasId = 'training-cost-chart-canvas-' . $__id;
        @endphp

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            {{-- BỘ LỌC --}}
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Bộ lọc</h3>
                    <span class="rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $periodLabel }}</span>
                </div>

                <div class="mt-3 space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Năm</label>
                        <select wire:model.live="year" class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                            @foreach($yearOptions as $y => $label)
                                <option value="{{ $y }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Tháng</label>
                        <select wire:model.live="month" class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                            <option value="">Cả năm</option>
                            @foreach($monthOptions as $m => $label)
                                <option value="{{ $m }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Loại hình đào tạo</label>
                        <div class="flex flex-wrap gap-2">
                            @forelse($trainingTypeOptions as $value => $label)
                                <button
                                    type="button"
                                    wire:click="toggleTrainingType('{{ $value }}')"
                                    class="rounded-full border px-3 py-1 text-xs transition
                                        @if(in_array($value, $selectedTrainingTypes ?? []))
                                            border-indigo-600 bg-indigo-50 text-indigo-700
                                        @else
                                            border-slate-300 bg-white text-slate-700 hover:bg-slate-50
                                        @endif
                                    "
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

            {{-- TỔNG THEO LOẠI HÌNH --}}
            <div class="rounded-lg border border-indigo-200 bg-white p-4 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-indigo-700">Chi phí theo loại hình</p>
                <ul class="space-y-2 text-sm">
                    @forelse(($typeTotals ?? []) as $key => $row)
                        @php
                            $isArray = is_array($row);
                            $isObject = is_object($row);
                            $label = $isArray ? ($row['label'] ?? (string) $key) : ($isObject ? ($row->label ?? (string) $key) : (string) $key);
                            $value = $isArray ? ($row['value'] ?? 0) : ($isObject ? ($row->value ?? 0) : (float) $row);
                        @endphp
                        <li class="flex items-center justify-between">
                            <span class="text-slate-700">{{ $label }}</span>
                            <span class="font-semibold text-slate-900">{{ number_format((float) $value, 0, ',', '.') }}</span>
                        </li>
                    @empty
                        <li class="text-xs text-slate-400">Không có dữ liệu.</li>
                    @endforelse
                </ul>

                <div class="mt-4 rounded-lg bg-slate-50 p-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-slate-700">Tổng chi phí</span>
                        <span class="text-lg font-bold text-slate-900">{{ number_format((float) ($totalCost ?? 0), 0, ',', '.') }} đ</span>
                    </div>
                </div>
            </div>

            {{-- BIỂU ĐỒ CHI PHÍ --}}
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-700">Biểu đồ chi phí</h3>
                <div class="relative h-[380px] w-full">
                    <canvas id="{{ $canvasId }}" wire:ignore></canvas>
                </div>

                {{-- JSON đặt ngoài canvas --}}
                <script type="application/json" id="chart-data-{{ $__id }}">@json($chartData)</script>
                <script type="application/json" id="chart-options-{{ $__id }}">@json($chartOptions)</script>

                @if(($tableData['hasData'] ?? false) && count($tableData['labels'] ?? []) === 1)
                    <div class="mt-2 flex justify-center">
                        <span class="rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                            {{ $tableData['labels'][0] }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- BẢNG DƯỚI BIỂU ĐỒ (nếu có nhiều cột) --}}
        @if($tableData['hasData'] ?? false)
            @if(count($tableData['labels'] ?? []) > 1)
                <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-[640px] divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
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
                                @php
                                    $rLabel = is_array($row) ? ($row['label'] ?? '') : (is_object($row) ? ($row->label ?? '') : '');
                                    $rVals  = is_array($row) ? ($row['values'] ?? []) : (is_object($row) ? ($row->values ?? []) : []);
                                    $rTotal = is_array($row) ? ($row['total'] ?? 0) : (is_object($row) ? ($row->total ?? 0) : 0);
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $rLabel }}</td>
                                    @foreach($rVals as $value)
                                        <td class="px-3 py-3 text-center text-slate-700">
                                            {{ number_format((float) $value, 0, ',', '.') }}
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                        {{ number_format((float) $rTotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Tổng</th>
                                @foreach(($tableData['columnTotals'] ?? []) as $value)
                                    <th class="px-3 py-3 text-center font-semibold text-slate-800">
                                        {{ number_format((float) $value, 0, ',', '.') }}
                                    </th>
                                @endforeach
                                <th class="px-4 py-3 text-right text-indigo-700">
                                    {{ number_format((float) ($totalCost ?? 0), 0, ',', '.') }} đ
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        @else
            <div class="mt-3 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                Chưa có dữ liệu để hiển thị.
            </div>
        @endif
    </x-filament::card>
</x-filament::widget>

@push('scripts')
<script>
(function () {
    const loadScriptSequence = (urls) => new Promise((resolve, reject) => {
        const tryNext = (i) => {
            if (i >= urls.length) return reject(new Error('Chart.js load failed'));
            const s = document.createElement('script');
            s.src = urls[i];
            s.async = true;
            s.onload = () => resolve();
            s.onerror = () => tryNext(i + 1);
            document.head.appendChild(s);
        };
        tryNext(0);
    });

    const ensureChart = () => {
        if (window.Chart) return Promise.resolve();
        const candidates = [
            '/vendor/chart.js/chart.umd.min.js',
            '/vendor/chart.js/chart.min.js',
            '/build/assets/chart.umd.js',
            '/build/assets/chart.js',
            '/js/chart.umd.min.js',
            'https://cdn.jsdelivr.net/npm/chart.js',
        ];
        return loadScriptSequence(candidates);
    };

    const init = () => {
        const id = @js($this->getId());
        const canvas = document.getElementById('training-cost-chart-canvas-' + id);
        if (!canvas) return;

        const parse = (elId) => {
            const el = document.getElementById(elId);
            if (!el) return null;
            try { return JSON.parse(el.textContent); } catch { return null; }
        };

        const getData = () => parse('chart-data-' + id) || { labels: [], datasets: [] };
        const getOpts = () => parse('chart-options-' + id) || {};

        const ctx = canvas.getContext('2d');
        window.__costCharts ??= {};
        const key = id;

        const render = () => {
            const data = getData();
            const options = getOpts();
            if (window.__costCharts[key]) {
                const c = window.__costCharts[key];
                c.config.type = 'bar';
                c.data = data;
                c.options = options;
                c.update();
            } else if (window.Chart) {
                window.__costCharts[key] = new Chart(ctx, { type: 'bar', data, options });
            }
        };

        render();
        document.addEventListener('livewire:load', render);
        document.addEventListener('livewire:navigated', render);
        if (window.Livewire?.hook) Livewire.hook('message.processed', () => render());
        new MutationObserver(render).observe(document.getElementById('chart-data-' + id), { childList: true });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => ensureChart().then(init));
    } else {
        ensureChart().then(init);
    }
})();
</script>
@endpush
