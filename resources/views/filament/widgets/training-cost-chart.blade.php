{{-- resources/views/filament/widgets/training-cost-chart.blade.php --}}
<x-filament::widget>
    <x-filament::card>
        <x-slot name="heading">
            <div class="flex flex-col gap-1">
                <span class="text-lg font-semibold text-slate-800">Thống kê chi phí đào tạo</span>
                <span class="text-sm text-slate-500">Theo dõi chi phí học viên hoàn thành theo từng loại hình và khoảng thời gian được chọn.</span>
            </div>
        </x-slot>

        @php
            $resolvedYear = $year ?? ($yearOptions ? array_key_first($yearOptions) : (int) now()->format('Y'));
            $periodLabel = $month ? sprintf('Tháng %02d/%d', $month, $resolvedYear) : 'Năm ' . $resolvedYear;
            $trainingTypeCount = is_array($trainingTypeOptions) ? count($trainingTypeOptions) : 0;
            $trainingTypeSize = min(max($trainingTypeCount ?: 4, 4), 10);
        @endphp

        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="lg:col-span-5 xl:col-span-4">
                    <div class="space-y-5 rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm backdrop-blur">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-1">
                            <label class="flex flex-col text-sm font-medium text-slate-600">
                                <span>Năm</span>
                                <select
                                    wire:model.live="year"
                                    class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-normal text-slate-700 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
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
                                    class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-normal text-slate-700 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                                >
                                    <option value="">Cả năm</option>
                                    @foreach($monthOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <label class="flex flex-col text-sm font-medium text-slate-600">
                            <span>Loại hình đào tạo</span>
                            <select
                                wire:model.live="selectedTrainingTypes"
                                multiple
                                size="{{ $trainingTypeSize }}"
                                class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-normal text-slate-700 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40 overflow-y-auto"
                                style="min-height: 10rem"
                            >
                                @foreach($trainingTypeOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="mt-2 text-xs font-normal text-slate-400">Giữ Ctrl hoặc Cmd để chọn nhiều loại hình.</span>
                        </label>
                    </div>
                </div>

                <div class="lg:col-span-7 xl:col-span-8 space-y-6">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase text-amber-500">Tổng chi phí</p>
                            <p class="mt-2 text-3xl font-semibold text-amber-600">{{ number_format($totalCost, 0, ',', '.') }}<span class="text-sm font-medium text-amber-500"> VND</span></p>
                            <p class="mt-1 text-xs text-amber-500/80">{{ $periodLabel }}</p>
                        </div>

                        <div class="lg:col-span-2 rounded-2xl border border-indigo-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase text-indigo-500">Chi phí theo loại hình</p>
                            @if(!empty($typeTotals))
                                <dl class="mt-3 grid gap-2 sm:grid-cols-2">
                                    @foreach($typeTotals as $type => $value)
                                        <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-3 py-2 text-sm text-indigo-600 shadow-sm">
                                            <dt class="font-medium">{{ $type }}</dt>
                                            <dd class="text-base font-semibold text-indigo-700">{{ number_format($value, 0, ',', '.') }} <span class="text-xs font-normal text-indigo-400">VND</span></dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @else
                                <p class="mt-3 text-sm text-indigo-400">Chưa có dữ liệu chi phí cho bộ lọc hiện tại.</p>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Biểu đồ chi phí</h3>
                        <div
                            class="relative mt-4 min-h-[22rem]"
                            x-data="dashboardChart({
                                type: 'bar',
                                data: @entangle('chartData').live,
                                options: @entangle('chartOptions').live,
                            })"
                        >
                            <canvas x-ref="canvas" class="h-full w-full rounded-xl bg-gradient-to-br from-amber-50 via-white to-slate-100"></canvas>

                            @if(empty($chartData['datasets']))
                                <div class="absolute inset-0 flex items-center justify-center rounded-xl bg-white/70 text-sm font-medium text-slate-400">
                                    Chưa có dữ liệu để hiển thị.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>

@include('filament.widgets.partials.dashboard-chart-script')
