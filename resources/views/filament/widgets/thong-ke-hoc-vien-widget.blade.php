<x-filament::widget>
    <x-filament::card class="space-y-6 p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-1.5">
                <h2 class="text-xl font-bold text-slate-800">
                    {{ static::$heading ?? 'Thống kê Học viên theo tháng' }}
                </h2>
                <p class="text-sm text-slate-500">
                    Theo dõi số lượng học viên đăng ký, hoàn thành và không hoàn thành theo từng tháng hoặc tháng cụ thể.
                </p>
            </div>

            @php
                $hasFilterForm = property_exists($this, 'form');
            @endphp

            @if ($hasFilterForm)
                <div class="w-full shrink-0 space-y-3 rounded-lg border border-slate-200 bg-slate-50/70 p-4 shadow-sm sm:w-auto sm:space-y-0 sm:border-0 sm:bg-transparent sm:p-0">
                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:justify-end">
                        {{ $this->form }}
                    </div>
                </div>
            @endif
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div
                x-data="dashboardChart()"
                x-init="init(); refresh({ type: 'bar', data: @js($this->getData()), options: @js($this->getOptions()) })"
                x-effect="refresh({ type: 'bar', data: @js($this->getData()), options: @js($this->getOptions()) })"
                class="relative w-full min-h-[260px] sm:min-h-[320px] lg:min-h-[360px]"
            >
                <canvas x-ref="canvas" class="h-full w-full"></canvas>
            </div>
        </div>

        @include('filament.widgets.partials.dashboard-chart-script')
    </x-filament::card>
</x-filament::widget>
