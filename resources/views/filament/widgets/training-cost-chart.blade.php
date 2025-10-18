{{-- resources/views/filament/widgets/training-cost-chart.blade.php --}}
<x-filament::widget>
    <x-filament::card>
        <x-slot name="heading">
            <div class="flex flex-col gap-1">
                <span class="text-lg font-semibold text-slate-800">Thống kê Chi phí đào tạo</span>
                <span class="text-sm text-slate-500">Theo dõi chi phí theo loại hình và khoảng thời gian được chọn.</span>
            </div>
        </x-slot>

        @php
            $resolvedYear = $year ?? ($yearOptions ? array_key_first($yearOptions) : (int) now()->format('Y'));
            $periodLabel = $month ? sprintf('%02d/%d', $month, $resolvedYear) : 'Năm ' . $resolvedYear;
        @endphp

        {{-- Lưới 3 cột full-width, hàng dưới spanning 3 cột là chart --}}
        <div class="grid gap-6 xl:grid-cols-3">
            {{-- Cột 1: Bộ lọc Năm/Tháng + Loại hình --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                <div class="space-y-5">
                    {{-- Dòng 1: Năm + Tháng trên cùng 1 dòng --}}
                    <div class="flex flex-wrap gap-4">
                        <label class="flex min-w-[140px] flex-1 flex-col text-sm font-medium text-slate-600">
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

                        <label class="flex min-w-[140px] flex-1 flex-col text-sm font-medium text-slate-600">
                            <span>Tháng</span>
                            <select
                                wire:model.live="month"
                                class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-normal text-slate-700 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                            >
                                <option value="">Tất cả</option>
                                @foreach($monthOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    {{-- Dòng 2: Loại hình đào tạo --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-600">Loại hình đào tạo</span>
                            @if(!empty($selectedTrainingTypes))
                                <button
                                    type="button"
                                    wire:click="clearTrainingTypeFilters"
                                    wire:loading.attr="disabled"
                                    class="text-xs font-semibold text-primary-600 transition hover:text-primary-500"
                                >
                                    Bỏ chọn
                                </button>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @forelse($trainingTypeOptions as $value => $label)
                                @php
                                    $isSelected = in_array($value, $selectedTrainingTypes ?? [], true);
                                @endphp
                                <button
                                    type="button"
                                    wire:key="training-type-{{ md5($value) }}"
                                    wire:click="toggleTrainingType({{ \Illuminate\Support\Js::from($value) }})"
                                    wire:loading.attr="disabled"
                                    @class([
                                        'rounded-full border px-3 py-1.5 text-xs font-semibold tracking-wide transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-500',
                                        'border-primary-500 bg-primary-500 text-white shadow-sm shadow-primary-200' => $isSelected,
                                        'border-slate-200 bg-white text-slate-600 hover:border-primary-400 hover:text-primary-600' => ! $isSelected,
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
            <div class="rounded-2xl border border-indigo-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">Chi phí theo loại hình</p>
                @if(!empty($typeTotals))
                    <dl class="mt-4 space-y-3">
                        @foreach($typeTotals as $type => $value)
                            <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-3 py-2 text-sm text-indigo-600 shadow-sm">
                                <dt class="font-medium">{{ $type }}</dt>
                                <dd class="text-base font-semibold text-indigo-700">{{ number_format($value, 0, ',', '.') }} <span class="text-xs font-normal text-indigo-400">VND</span></dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="mt-4 text-sm text-indigo-400">Chưa có dữ liệu chi phí cho bộ lọc hiện tại.</p>
                @endif
            </div>

            {{-- Cột 3: Tổng chi phí --}}
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-500">Tổng chi phí</p>
                <p class="mt-3 text-3xl font-semibold text-amber-600">{{ number_format($totalCost, 0, ',', '.') }}<span class="text-sm font-medium text-amber-500"> VND</span></p>
                <p class="mt-2 text-xs text-amber-500/80">{{ $periodLabel }}</p>
            </div>

            {{-- Hàng dưới (span 3 cột): Biểu đồ chi phí theo THÁNG của NĂM đã chọn --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm xl:col-span-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Biểu đồ chi phí</h3>
                <div
                    class="relative mt-4 h-[22rem] w-full sm:h-[26rem] lg:h-[28rem]"
                    wire:ignore
                    x-data="dashboardChart({
                        type: 'bar',
                        data: @entangle('chartData').live,
                        options: @entangle('chartOptions').live,
                    })"
                >
                    <canvas x-ref="canvas" class="h-full w-full rounded-xl bg-gradient-to-br from-amber-50 via-white to-slate-100"></canvas>
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>

{{-- Giữ include tại đây (an toàn), đồng thời file này cũng được nạp toàn cục trong AdminPanelProvider --}}
@include('filament.widgets.partials.dashboard-chart-script')
