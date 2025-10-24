<?php

namespace App\Filament\Widgets;

use App\Models\KhoaHoc; // Chỉ cần model KhoaHoc để lấy options
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Computed;

class ThongKeHocVienFilterWidget extends Widget
{
    // Sử dụng view Blade riêng chỉ cho bộ lọc
    protected static string $view = 'filament.widgets.thong-ke-hoc-vien-filter-widget';
    protected static ?int $sort = 5; // Hiển thị trên cùng
    protected int|string|array $columnSpan = 12; // Chiếm cả hàng

    // ===== Trạng thái Livewire cho bộ lọc =====
    public ?int $year = null; // Cho phép chọn năm
    /** @var array<int,string> */
    public array $selectedTrainingTypes = [];

    // Cache
    protected ?Collection $planYearCache = null;
    protected ?array $trainingTypeOptionsCache = null;

    // ===== Lifecycle =====
    public function mount(): void
    {
        $this->year = $this->getDefaultYear(); // Mặc định năm hiện tại/mới nhất
        $this->selectedTrainingTypes = [];
    }

    // Các hook updated để xử lý khi bộ lọc thay đổi (ví dụ: dispatch event)
    public function updatedYear($value): void
    {
        Log::info("FilterWidget - Year selected: {$value}");
        // Có thể dispatch event để widget khác (nếu có) lắng nghe
        // $this->dispatch('filtersUpdated', year: $this->year, types: $this->selectedTrainingTypes);
    }
    public function updatedSelectedTrainingTypes(): void
    {
         Log::info("FilterWidget - Types selected: " . implode(', ', $this->selectedTrainingTypes));
        // Có thể dispatch event
        // $this->dispatch('filtersUpdated', year: $this->year, types: $this->selectedTrainingTypes);
    }

    // ===== Actions cho Bộ lọc =====
    public function toggleTrainingType(string $value): void
    {
        $idx = array_search($value, $this->selectedTrainingTypes, true);
        if ($idx === false) $this->selectedTrainingTypes[] = $value;
        else array_splice($this->selectedTrainingTypes, $idx, 1);
        // updatedSelectedTrainingTypes sẽ được gọi
    }
    public function clearTrainingTypeFilters(): void
    {
        $this->selectedTrainingTypes = [];
        // updatedSelectedTrainingTypes sẽ được gọi
    }

    // ===== Helpers Lấy Options Bộ lọc =====
    #[Computed] // Cache options trong request
    public function yearOptions(): array
    {
        return $this->planYears()
            ->mapWithKeys(fn ($year) => [$year => (string) $year])
            ->all();
    }

    #[Computed(persist: true, seconds: 3600)] // Cache lâu hơn
    public function trainingTypeOptions(): array
    {
        if ($this->trainingTypeOptionsCache !== null) return $this->trainingTypeOptionsCache;
        $table = (new KhoaHoc)->getTable(); if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'loai_hinh_dao_tao')) return [];
        try { $options = KhoaHoc::query()->whereNotNull('loai_hinh_dao_tao')->where('loai_hinh_dao_tao', '!=', '')->distinct()->orderBy('loai_hinh_dao_tao')->pluck('loai_hinh_dao_tao')->mapWithKeys(fn ($label) => [$label => $this->formatTrainingTypeLabel($label)])->all(); return $this->trainingTypeOptionsCache = $options; } catch (\Exception $e) { Log::error("Get training types error: ".$e->getMessage()); return []; }
    }

    protected function getDefaultYear(): int
    {
        $years = $this->yearOptions(); // Gọi computed property
        $currentYear = now()->year;
        return isset($years[$currentYear]) ? $currentYear : (int) (array_key_first($years) ?? $currentYear);
    }

    protected function planYears(): Collection
    {
        if ($this->planYearCache !== null) return $this->planYearCache;
        $years = collect();
        if (Schema::hasTable('lich_hocs')) {
            if (Schema::hasColumn('lich_hocs', 'nam')) { $years = $years->merge(DB::table('lich_hocs')->whereNotNull('nam')->distinct()->orderByDesc('nam')->pluck('nam')); }
            elseif (Schema::hasColumn('lich_hocs', 'ngay_hoc')) { $years = $years->merge(DB::table('lich_hocs')->whereNotNull('ngay_hoc')->selectRaw('DISTINCT YEAR(ngay_hoc) as y')->orderByDesc('y')->pluck('y')); }
        }
        if ($years->isEmpty() && Schema::hasTable('khoa_hocs')) {
             $col = Schema::hasColumn('khoa_hocs', 'ngay_bat_dau') ? 'ngay_bat_dau' : 'created_at';
             if(Schema::hasColumn('khoa_hocs', $col)) { $years = DB::table('khoa_hocs')->whereNotNull($col)->selectRaw("DISTINCT YEAR($col) as y")->orderByDesc('y')->pluck('y'); }
        }
        $now = now()->year; if ($years->isEmpty()) $years = collect([$now]); elseif (!$years->contains($now)) $years->prepend($now)->sortDesc();
        return $this->planYearCache = $years->map(fn ($v) => filter_var($v, FILTER_VALIDATE_INT))->filter(fn ($v) => $v !== false && $v > 1900)->unique()->values();
    }

    private function formatTrainingTypeLabel(string $label): string
    {
        $value = trim((string) $label); if ($value === '') return $label; $clean = $value;
        try { $clean = preg_replace('/^[Vv✓✔☑✅•\-\/\s]+/u','',$value)??$value; $clean = preg_replace('/^[-–—]\s*/u','',$clean)??$clean; $clean = preg_replace('/[✓✔☑✅]+/u','',$clean)??$clean; $clean = preg_replace('/\b[Vv]\b/u','',$clean)??$clean; $clean = preg_replace('/\s{2,}/u',' ',$clean)??$clean; } catch (\Exception $e) { $clean = $value; }
        $normalized = trim($clean, " \t\n\r\0\x0B-–—"); return $normalized !== '' ? $normalized : $value;
    }
}
