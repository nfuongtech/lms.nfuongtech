<x-filament::page>
    <div class="space-y-6">
        @once
            <style>
                /* Thẻ “Đang áp dụng lọc” */
                .fi-ta-filter-indicators > span:first-child { display: none; }
                .fi-ta-filter-indicators::before {
                    content: 'Đang áp dụng lọc';
                    margin-right: .5rem;
                    font-size: .75rem; font-weight: 600; text-transform: uppercase;
                    color: rgb(55 65 81);
                }

                /* Ẩn hoàn toàn actions ở header mặc định để tránh lặp (actions vẫn mount để chạy được) */
                .fi-page-header .fi-actions,
                .fi-header .fi-actions,
                .fi-page-header-actions,
                .fi-header-actions { display:none !important; }

                /* Ẩn nút Filters của bảng dưới (đa ngôn ngữ) */
                .fi-ta-header .fi-ta-filters-trigger,
                .fi-ta-header [data-fi-action="open-filters"],
                .fi-ta-header [dusk="filament.tables.filters.toggle-button"] {
                    display: none !important;
                }

                /* Token multi-select look */
                .token-input{ border:1px solid #e5e7eb;border-radius:.5rem;padding:.25rem .5rem;min-height:2.5rem;display:flex;flex-wrap:wrap;gap:.25rem;align-items:center;background:#fff;}
                .token-chip{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:.375rem;padding:.125rem .5rem;font-size:.75rem;display:flex;align-items:center;gap:.25rem}
                .token-chip button{line-height:1;border:none;background:transparent;cursor:pointer}
                .token-search{border:none;outline:none;min-width:10ch;flex:1 0 auto;font-size:.875rem;padding:.25rem}
                .token-dropdown{position:absolute;z-index:40;background:#fff;border:1px solid #e5e7eb;border-radius:.5rem;box-shadow:0 10px 15px rgba(0,0,0,.05);max-height:18rem;overflow:auto;margin-top:.25rem;width:100%;}
                .token-item{padding:.5rem .75rem;cursor:pointer}
                .token-item:hover{background:#f8fafc}
                .token-item.active{background:#eff6ff}
            </style>
        @endonce

        {{-- Thêm JS backup ẩn nút Filters nếu CSS trên không bắt được --}}
        <script>
            function hideFilamentFilterButtons(){
                document.querySelectorAll('.fi-ta-header button, .fi-ta-header a').forEach(el=>{
                    const t=(el.textContent||'').trim().toLowerCase();
                    if(['filters','filter','chọn lọc thông tin'].includes(t)) el.style.display='none';
                });
            }
            document.addEventListener('DOMContentLoaded', hideFilamentFilterButtons);
            document.addEventListener('livewire:navigated', hideFilamentFilterButtons);
            document.addEventListener('livewire:load', hideFilamentFilterButtons);
        </script>

        @php($selectedCourses = $this->selectedCourseIds ?? [])
        @php($totals = $this->summaryTotals)

        {{-- =================== KHỐI TỔNG QUAN + NÚT LỆNH =================== --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4 border-b">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-1">
                        <h2 class="text-base font-semibold text-gray-900">Tổng quan khóa học</h2>
                        <p class="text-xs text-gray-500">
                            Nhấn vào hàng trong bảng để chọn/bỏ chọn khóa học. Bảng "Danh sách học viên hoàn thành" sẽ tự lọc theo các khóa đã chọn.
                        </p>
                    </div>

                    {{-- Render đúng thứ tự bằng chính header actions đã mount (bảo đảm click chạy) --}}
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <x-filament-actions::actions
                            :actions="$this->getOverviewActions()"
                            :alignment="'end'"
                            :full-width="false"
                            class="gap-2"
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-center font-semibold">TT</th>
                            <th class="px-3 py-2 font-semibold">Mã khóa</th>
                            <th class="px-3 py-2 font-semibold">Tên khóa học</th>
                            <th class="px-3 py-2 font-semibold">Trạng thái</th>
                            <th class="px-3 py-2 text-center font-semibold">Tổng số giờ</th>
                            <th class="px-3 py-2 font-semibold">Giảng viên</th>
                            <th class="px-3 py-2 font-semibold">Thời gian đào tạo</th>
                            <th class="px-3 py-2 text-center font-semibold">Số lượng HV</th>
                            <th class="px-3 py-2 text-center font-semibold">Hoàn thành</th>
                            <th class="px-3 py-2 text-center font-semibold">Không hoàn thành</th>
                            <th class="px-3 py-2 text-center font-semibold">Tổng thu</th>
                            <th class="px-3 py-2 font-semibold">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php($map = array_flip($selectedCourses ?? []))
                        @forelse($this->summaryRows as $row)
                            @php($isSelected = isset($map[$row['id']]))
                            <tr
                                wire:key="summary-{{ $row['id'] }}"
                                wire:click="selectCourseFromSummary({{ $row['id'] }})"
                                class="cursor-pointer transition {{ $isSelected ? 'bg-primary-50' : 'bg-white hover:bg-primary-50' }}"
                            >
                                <td class="px-3 py-2 text-center font-medium text-gray-900">{{ $row['index'] }}</td>
                                <td class="px-3 py-2 font-medium text-gray-900">{{ $row['ma_khoa'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $row['ten_khoa'] }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $this->statusBadgeClass($row['trang_thai'] ?? null) }} whitespace-nowrap">
                                        {{ $row['trang_thai'] ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center text-gray-700">{{ $row['tong_gio'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $row['giang_vien'] }}</td>
                                <td class="px-3 py-2 text-gray-700 whitespace-pre-line">{{ $row['thoi_gian'] }}</td>
                                <td class="px-3 py-2 text-center text-gray-700">{{ number_format($row['so_luong_hv'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-emerald-600 font-semibold">{{ number_format($row['hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-rose-600 font-semibold">{{ number_format($row['khong_hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-gray-700">{{ $row['tong_thu'] > 0 ? number_format($row['tong_thu'], 0, ',', '.') : '-' }}</td>
                                <td class="px-3 py-2 text-gray-600">{{ $row['ghi_chu'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-3 py-4 text-center text-sm text-gray-500">Chưa có khóa học phù hợp với bộ lọc.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($this->summaryRows->isNotEmpty())
                        @php($totals = $this->summaryTotals)
                        <tfoot class="bg-slate-50 text-sm font-semibold text-gray-700">
                            <tr>
                                <td colspan="7" class="px-3 py-2 text-right">Tổng cộng</td>
                                <td class="px-3 py-2 text-center">{{ number_format($totals['so_luong_hv'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-emerald-600">{{ number_format($totals['hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center text-rose-600">{{ number_format($totals['khong_hoan_thanh'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-center">{{ $totals['tong_thu'] > 0 ? number_format($totals['tong_thu'], 0, ',', '.') : '-' }}</td>
                                <td class="px-3 py-2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- =================== BỘ LỌC NHANH (1 HÀNG) =================== --}}
        @php($years  = $this->availableYears)
        @php($months = $this->availableMonths)
        @php($weeks  = $this->availableWeeks)
        @php($trainingOptions = $this->getTrainingTypeOptions())

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-3 border-b">
                {{-- Giữ 1 hàng, co giãn khi màn hình nhỏ --}}
                <div class="flex flex-row flex-wrap items-end gap-3">
                    {{-- Năm --}}
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600">Năm</label>
                        <select
                            class="fi-input fi-input-wrp-input rounded-lg border-gray-300 text-sm"
                            wire:model.defer="tableFilters.bo_loc.data.year"
                            wire:change="applyQuickFilters"
                        >
                            @forelse($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @empty
                                <option value="{{ now()->year }}">{{ now()->year }}</option>
                            @endforelse
                        </select>
                    </div>

                    {{-- Tháng --}}
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600">Tháng</label>
                        <select
                            class="fi-input fi-input-wrp-input rounded-lg border-gray-300 text-sm"
                            wire:model.defer="tableFilters.bo_loc.data.month"
                            wire:change="applyQuickFilters"
                        >
                            <option value="">--</option>
                            @foreach($months as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tuần --}}
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600">Tuần</label>
                        <select
                            class="fi-input fi-input-wrp-input rounded-lg border-gray-300 text-sm"
                            wire:model.defer="tableFilters.bo_loc.data.week"
                            wire:change="applyQuickFilters"
                        >
                            <option value="">--</option>
                            @foreach($weeks as $w)
                                <option value="{{ $w }}">{{ $w }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Từ ngày --}}
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600">Từ ngày</label>
                        <input type="date"
                            class="fi-input fi-input-wrp-input rounded-lg border-gray-300 text-sm"
                            wire:model.defer="tableFilters.bo_loc.data.from_date"
                            wire:change="applyQuickFilters" />
                    </div>

                    {{-- Đến ngày --}}
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600">Đến ngày</label>
                        <input type="date"
                            class="fi-input fi-input-wrp-input rounded-lg border-gray-300 text-sm"
                            wire:model.defer="tableFilters.bo_loc.data.to_date"
                            wire:change="applyQuickFilters" />
                    </div>

                    {{-- Loại hình đào tạo (từ Quy tắc mã khóa / lich_hocs.loai_hinh) --}}
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600">Loại hình đào tạo</label>
                        <div class="flex items-center flex-wrap gap-3">
                            @foreach($trainingOptions as $key => $label)
                                <label class="inline-flex items-center gap-1 text-xs text-gray-700">
                                    <input type="checkbox"
                                        wire:model.defer="tableFilters.bo_loc.data.training_types"
                                        wire:change="applyQuickFilters"
                                        value="{{ $key }}"
                                        class="border-gray-300 rounded" />
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Khóa học: token multi-select (entangle với selectedCourseIds) --}}
                <div class="mt-3">
                    <label class="text-xs font-semibold text-gray-600">Khóa học</label>
                    <div
                        x-data="courseTokens({
                            allOptions: @js(($this->summaryRows ?? collect())->map(fn($r)=>['id'=>$r['id'],'code'=>$r['ma_khoa'],'name'=>$r['ten_khoa']])->values()),
                            selected: @entangle('selectedCourseIds').live,
                            apply: () => { $wire.applyQuickFilters(); }
                        })"
                        class="relative"
                    >
                        <div class="token-input" @click="$refs.search.focus()">
                            <template x-for="opt in tokenState.selectedObjects" :key="opt.id">
                                <span class="token-chip">
                                    <span x-text="opt.code"></span>
                                    <button type="button" @click.stop="remove(opt.id)" aria-label="Remove">×</button>
                                </span>
                            </template>

                            <input x-ref="search" class="token-search" type="text" placeholder="Tìm khóa học..."
                                   x-model="tokenState.query"
                                   @keydown.down.prevent="move(1)"
                                   @keydown.up.prevent="move(-1)"
                                   @keydown.enter.prevent="pickActive()"
                                   @focus="open=true" @blur="closeLater()" />
                        </div>

                        <div class="token-dropdown" x-show="open" x-transition @mousedown.prevent>
                            <template x-for="(opt,idx) in filtered()" :key="opt.id">
                                <div class="token-item" :class="{'active': idx===activeIndex}" @mousemove="activeIndex=idx" @click="toggle(opt.id)">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-medium" x-text="opt.code"></div>
                                            <div class="text-xs text-gray-500 truncate" x-text="opt.name"></div>
                                        </div>
                                        <div class="text-xs" x-text="isSelected(opt.id)?'Đã chọn':''"></div>
                                    </div>
                                </div>
                            </template>
                            <div class="token-item text-gray-500" x-show="filtered().length===0">Không có khóa học phù hợp</div>
                        </div>

                        <div class="mt-2 flex items-center gap-2">
                            <button type="button" class="fi-btn fi-btn-sm border border-gray-300 bg-white hover:bg-gray-50" @click="apply()">
                                Áp dụng
                            </button>
                            <button type="button" class="fi-btn fi-btn-sm border border-gray-200" style="background-color:#FFF0F0;color:#8B0000;" @click="clear(); apply()">
                                Xóa chọn khóa
                            </button>
                        </div>

                        <script>
                            function courseTokens(cfg){
                                return {
                                    tokenState: {
                                        allOptions: (cfg.allOptions || []).map(o=>({id:Number(o.id), code:o.code, name:o.name})),
                                        get selected(){ return cfg.selected },
                                        set selected(v){ cfg.selected = v },
                                        get selectedObjects(){
                                            const ids = (cfg.selected || []).map(Number);
                                            return this.allOptions.filter(o => ids.includes(o.id));
                                        },
                                        query: '',
                                    },
                                    open:false, activeIndex:0,
                                    filtered(){
                                        const q = this.tokenState.query.toLowerCase().trim();
                                        let list = this.tokenState.allOptions;
                                        if(q){
                                            list = list.filter(o => (o.code||'').toLowerCase().includes(q) || (o.name||'').toLowerCase().includes(q));
                                        }
                                        return list.slice(0,100);
                                    },
                                    isSelected(id){ return (cfg.selected || []).map(Number).includes(Number(id)); },
                                    toggle(id){
                                        id = Number(id);
                                        let arr = (cfg.selected || []).map(Number);
                                        if (arr.includes(id)) arr = arr.filter(i=>i!==id); else arr.push(id);
                                        cfg.selected = arr;
                                        this.$refs.search.focus();
                                    },
                                    remove(id){
                                        id = Number(id);
                                        cfg.selected = (cfg.selected || []).map(Number).filter(i=>i!==id);
                                    },
                                    clear(){ cfg.selected = []; },
                                    move(step){
                                        const len=this.filtered().length;
                                        if(!len) return;
                                        this.activeIndex = (this.activeIndex + step + len) % len;
                                    },
                                    pickActive(){
                                        const item=this.filtered()[this.activeIndex]; if(!item) return; this.toggle(item.id);
                                    },
                                    closeLater(){ setTimeout(()=>this.open=false, 120); },
                                    apply: cfg.apply
                                }
                            }
                        </script>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <h2 class="text-lg font-semibold text-gray-900">Danh sách học viên hoàn thành</h2>
                <div class="mt-3">
                    {{ $this->table }}
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
