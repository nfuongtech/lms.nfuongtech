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
                        class="mt-1 min-w-[11rem] rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-normal text-slate-700 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
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
                <canvas x-ref="canvas" class="rounded-xl bg-gradient-to-br from-slate-50 to-white shadow-inner"></canvas>
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

@include('filament.widgets.includes.dashboard-chart-script')
