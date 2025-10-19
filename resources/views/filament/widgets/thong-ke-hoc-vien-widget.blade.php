<x-filament::widget>
    <x-filament::card>
        <div class="text-base font-semibold text-slate-800 mb-2">
            {{ static::$heading ?? 'Thống kê Học viên theo tháng' }}
        </div>

        {{-- CSS fix responsive (không dựa vào grid Laravel nữa) --}}
        <style>
            [data-dashboard-chart-wrapper]{width:100%;min-width:0;}
            [data-dashboard-chart-canvas]{width:100% !important;height:320px !important;display:block;}
            @media (min-width:1024px){
                [data-dashboard-chart-canvas]{height:360px !important;}
            }
            .fi-widget canvas{max-width:100% !important;width:100% !important;display:block;}
        </style>

        {{-- Alpine + Chart.js tự khởi tạo (dùng script chung) --}}
        <div
            data-dashboard-chart-wrapper
            x-data="dashboardChart({
                type: 'bar',
                data: @js($this->getData()),
                options: @js($this->getOptions()),
            })"
            x-init="init()"
            style="position:relative;"
        >
            <canvas x-ref="canvas" data-dashboard-chart-canvas></canvas>
        </div>

        @once
            @push('scripts')
                {{-- Nạp script chung cho toàn Dashboard (đăng ký plugin + helpers) --}}
                @include('filament.widgets.partials.dashboard-chart-script')
            @endpush
        @endonce
    </x-filament::card>
</x-filament::widget>
