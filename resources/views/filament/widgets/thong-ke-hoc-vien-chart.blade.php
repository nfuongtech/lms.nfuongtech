{{-- resources/views/filament/widgets/thong-ke-hoc-vien-chart.blade.php --}}
<x-filament::widget>
    <x-filament::card class="p-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Thống kê Học viên theo tháng</h2>
                <p class="mt-1 text-sm text-slate-500">Lọc theo Năm (từ Lịch học/Kế hoạch) &amp; Loại hình đào tạo.</p>
            </div>
        </div>

        @php
            $__id = $this->getId();
            $periodLabel = $month ? ('Tháng ' . sprintf('%02d/%d', $month, $year)) : ('Năm ' . $year);
        @endphp

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            {{-- BỘ LỌC --}}
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Bộ lọc</h3>
                    <span class="rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $periodLabel }}</span>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Năm</label>
                        <select wire:model.live="year" class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                            @foreach($this->yearOptions as $y => $label)
                                <option value="{{ $y }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Tháng</label>
                        <select wire:model.live="month" class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                            <option value="">Cả năm</option>
                            @foreach($this->monthOptions as $m => $label)
                                <option value="{{ $m }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Loại hình đào tạo</label>
                        <div class="flex flex-wrap gap-2">
                            @forelse($this->trainingTypeOptions as $value => $label)
                                <button
                                    type="button"
                                    wire:click="toggleTrainingType('{{ $value }}')"
                                    class="rounded-full border px-3 py-1 text-xs transition
                                        @if(in_array($value, $selectedTrainingTypes ?? []))
                                            border-indigo-600 bg-indigo-50 text-indigo-700
                                        @else
                                            border-slate-300 bg-white text-slate-700 hover:bg-slate-50
                                        @endif"
                                >{{ $label }}</button>
                            @empty
                                <p class="text-xs text-slate-400">Chưa có dữ liệu loại hình đào tạo.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- BIỂU ĐỒ --}}
            <div class="md:col-span-2 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-700">Biểu đồ</h3>
                <div class="relative h-[380px] w-full">
                    <canvas id="hv-monthly-chart-{{ $__id }}" wire:ignore></canvas>
                </div>

                {{-- payloads đặt ngoài canvas --}}
                <script type="application/json" id="hv-chart-data-{{ $__id }}">@json($this->chartData)</script>
                <script type="application/json" id="hv-chart-options-{{ $__id }}">@json($this->chartOptions)</script>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>

@push('scripts')
<script>
(function () {
    /** Try local assets first (avoid CSP), then CDN */
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
        const canvas = document.getElementById('hv-monthly-chart-' + id);
        if (!canvas) return;

        const parse = (elId) => {
            const el = document.getElementById(elId);
            if (!el) return null;
            try { return JSON.parse(el.textContent); } catch { return null; }
        };

        const getData = () => parse('hv-chart-data-' + id) || { labels: [], datasets: [] };
        const getOpts = () => parse('hv-chart-options-' + id) || {};

        const ctx = canvas.getContext('2d');
        window.__hvCharts ??= {};
        const key = id;

        const render = () => {
            const data = getData();
            const options = getOpts();
            if (window.__hvCharts[key]) {
                const c = window.__hvCharts[key];
                c.config.type = 'bar';
                c.data = data;
                c.options = options;
                c.update();
            } else if (window.Chart) {
                window.__hvCharts[key] = new Chart(ctx, { type: 'bar', data, options });
            }
        };

        render();
        // Livewire 3 hooks
        document.addEventListener('livewire:load', render);
        document.addEventListener('livewire:navigated', render);
        if (window.Livewire?.hook) Livewire.hook('message.processed', () => render());
        // Mutation fallback (nếu Livewire thay JSON)
        new MutationObserver(render).observe(document.getElementById('hv-chart-data-' + id), { childList: true });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => ensureChart().then(init));
    } else {
        ensureChart().then(init);
    }
})();
</script>
@endpush
