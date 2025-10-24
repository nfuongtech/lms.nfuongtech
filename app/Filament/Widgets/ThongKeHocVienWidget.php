<?php

namespace App\Filament\Widgets;

use App\Models\DangKy;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use App\Models\KhoaHoc;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Computed;

class ThongKeHocVienWidget extends Widget
{
    protected static string $view = 'filament.widgets.thong-ke-hoc-vien-widget';
    protected static ?int $sort = 10;
    protected int|string|array $columnSpan = 12;
    protected static bool $isLazy = false;

    // ===== Livewire state =====
    public ?int $year = null;
    /** @var array<int,string> */
    public array $selectedTrainingTypes = [];

    // ===== Chart payload for Alpine (entangle) =====
    /** @var array<string,mixed> */
    public array $chartPayload = [];
    /** @var array<string,mixed> */
    public array $chartOptionsPayload = [];

    // ===== Cache =====
    /** @var array<string, array<int, int[]>> [cacheKey => [month => courseIds[]]] */
    public array $courseMonthCache = []; // public để reset
    protected ?Collection $planYearCache = null;

    // ===== Lifecycle =====
    public function mount(): void
    {
        $this->year = $this->getDefaultYear();
        $this->refreshChartPayload(); // đảm bảo có chart ngay lần đầu
    }

    public function updated($property): void
    {
        if ($property === 'year' || $property === 'selectedTrainingTypes') {
            $this->reset('courseMonthCache'); // Xoá cache khi filter đổi
            $this->refreshChartPayload();     // cập nhật dữ liệu chart ngay
        }
    }

    // ===== Actions cho Bộ lọc =====
    public function toggleTrainingType(string $value): void
    {
        $idx = array_search($value, $this->selectedTrainingTypes, true);
        if ($idx === false) $this->selectedTrainingTypes[] = $value;
        else array_splice($this->selectedTrainingTypes, $idx, 1);
        $this->refreshChartPayload();
    }

    public function clearTrainingTypeFilters(): void
    {
        $this->selectedTrainingTypes = [];
        $this->refreshChartPayload();
    }

    public function selectAllTrainingTypes(): void
    {
        $this->selectedTrainingTypes = array_keys($this->trainingTypeOptions);
        $this->refreshChartPayload();
    }

    // ===== Computed: Options =====
    #[Computed]
    public function yearOptions(): array
    {
        $opts = $this->planYears()->mapWithKeys(fn ($y) => [$y => (string) $y])->all();
        $now = (int) now()->year;
        if (!array_key_exists($now, $opts)) {
            $opts = [$now => (string) $now] + $opts;
        }
        return $opts;
    }

    /**
     * Lấy options Loại hình đào tạo từ nhiều nguồn (ưu tiên KhoaHoc, HV Hoàn thành, Đăng ký;
     * nếu thiếu thì suy luận từ mã khóa bằng bảng quy tắc mã/prefix/regex hoặc tiền tố mã).
     */
    #[Computed]
    public function trainingTypeOptions(): array
    {
        $khTable = (new KhoaHoc())->getTable();
        if (! Schema::hasTable($khTable)) {
            return [];
        }

        $types = collect();

        if (Schema::hasColumn($khTable, 'loai_hinh_dao_tao')) {
            try {
                $types = KhoaHoc::query()
                    ->whereNotNull('loai_hinh_dao_tao')
                    ->where('loai_hinh_dao_tao', '!=', '')
                    ->distinct()
                    ->orderBy('loai_hinh_dao_tao')
                    ->pluck('loai_hinh_dao_tao');
            } catch (\Throwable) {
                $types = collect();
            }
        }

        if ($types->isEmpty() && Schema::hasColumn($khTable, 'chuong_trinh_id')
            && Schema::hasTable('chuong_trinhs')
            && Schema::hasColumn('chuong_trinhs', 'loai_hinh_dao_tao')) {
            try {
                $types = DB::table($khTable)
                    ->join('chuong_trinhs', 'khoa_hocs.chuong_trinh_id', '=', 'chuong_trinhs.id')
                    ->whereNotNull('chuong_trinhs.loai_hinh_dao_tao')
                    ->where('chuong_trinhs.loai_hinh_dao_tao', '!=', '')
                    ->distinct()
                    ->orderBy('chuong_trinhs.loai_hinh_dao_tao')
                    ->pluck('chuong_trinhs.loai_hinh_dao_tao');
            } catch (\Throwable) {
                $types = collect();
            }
        }

        if ($types->isEmpty()) {
            return [];
        }

        return $types
            ->map(fn ($t) => trim((string) $t))
            ->filter()
            ->unique()
            ->mapWithKeys(fn ($val) => [$val => $this->formatTrainingTypeLabel($val)])
            ->sort()
            ->all();
    }

    // ===== Computed: Table & Chart =====
    #[Computed]
    public function monthlySummaryTableData(): array
    {
        if ($this->year === null) return array_fill(1, 12, ['dk' => 0, 'ht' => 0, 'kht' => 0]);

        $year = (int) $this->year;
        $monthly = array_fill(1, 12, ['dk' => 0, 'ht' => 0, 'kht' => 0]);

        $courseMap = $this->courseIdsByMonth($year);
        if (empty(array_filter($courseMap))) return $monthly;

        $allCourseIds = collect($courseMap)->flatten()->unique()->values()->all();
        if (empty($allCourseIds)) return $monthly;

        try {
            $dangKyCounts = DangKy::whereIn('khoa_hoc_id', $allCourseIds)
                ->selectRaw('khoa_hoc_id, COUNT(DISTINCT id) as c')->groupBy('khoa_hoc_id')->pluck('c','khoa_hoc_id');

            $hoanThanhCounts = HocVienHoanThanh::whereIn('khoa_hoc_id', $allCourseIds)
                ->selectRaw('khoa_hoc_id, COUNT(DISTINCT id) as c')->groupBy('khoa_hoc_id')->pluck('c','khoa_hoc_id');

            $khongHoanThanhCounts = collect();
            $khtTable = (new HocVienKhongHoanThanh())->getTable();
            if (Schema::hasTable($khtTable)) {
                $khongHoanThanhCounts = HocVienKhongHoanThanh::whereIn('khoa_hoc_id', $allCourseIds)
                    ->selectRaw('khoa_hoc_id, COUNT(DISTINCT id) as c')->groupBy('khoa_hoc_id')->pluck('c','khoa_hoc_id');
            }

            foreach ($courseMap as $month => $cids) {
                if (empty($cids)) continue;
                $dk = 0; $ht = 0; $kht = 0;
                foreach ($cids as $cid) {
                    $dk  += (int) ($dangKyCounts[$cid] ?? 0);
                    $ht  += (int) ($hoanThanhCounts[$cid] ?? 0);
                    $kht += (int) ($khongHoanThanhCounts[$cid] ?? 0);
                }
                if ($khongHoanThanhCounts->isEmpty()) $kht = max(0, $dk - $ht);
                $monthly[$month] = ['dk' => $dk, 'ht' => $ht, 'kht' => $kht];
            }
        } catch (\Throwable) {
            return array_fill(1, 12, ['dk' => 0, 'ht' => 0, 'kht' => 0]);
        }

        return $monthly;
    }

    #[Computed]
    public function chartData(): array
    {
        $m = $this->monthlySummaryTableData;
        return [
            'labels'   => collect(range(1, 12))->map(fn ($i) => sprintf('T%02d', $i))->all(),
            'datasets' => [
                $this->makeBarDataset('ĐK',  collect($m)->pluck('dk')->all(),  'dang-ky'),
                $this->makeBarDataset('HT',  collect($m)->pluck('ht')->all(),  'hoan-thanh'),
                $this->makeBarDataset('KHT', collect($m)->pluck('kht')->all(), 'khong-hoan-thanh'),
            ],
        ];
    }

    #[Computed]
    public function chartOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                        'boxWidth' => 12,
                        'color' => '#1e293b',
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(15, 23, 42, 0.95)',
                    'titleFont' => ['weight' => '600'],
                    'padding' => 12,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => ['mode' => 'index', 'intersect' => false],
            'layout' => ['padding' => ['top' => 24, 'right' => 16, 'bottom' => 12, 'left' => 8]],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => [
                        'color' => '#475569',
                        'font' => ['size' => 12, 'weight' => '500'],
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                        'color' => '#475569',
                        'font' => ['size' => 12, 'weight' => '500'],
                    ],
                    'grid' => [
                        'color' => 'rgba(148,163,184,0.18)',
                        'drawBorder' => false,
                    ],
                ],
            ],
            'datasets' => [
                'bar' => [
                    'borderRadius' => 8,
                    'maxBarThickness' => 60,
                ],
            ],
            'animation' => ['duration' => 900, 'easing' => 'easeOutQuart'],
        ];
    }

    // ===== Sync chart payloads to Alpine =====
    private function refreshChartPayload(): void
    {
        $this->chartPayload        = $this->chartData;     // computed -> array
        $this->chartOptionsPayload = $this->chartOptions;  // computed -> array
    }

    // ===== Helpers: Years =====
    protected function getDefaultYear(): int
    {
        $opts = $this->yearOptions;
        $now = (int) now()->year;
        return array_key_exists($now, $opts) ? $now : (int) (array_key_first($opts) ?? $now);
    }

    protected function planYears(): Collection
    {
        if ($this->planYearCache !== null) return $this->planYearCache;

        $years = collect();
        $khoaHocTable = (new KhoaHoc())->getTable();

        if (Schema::hasTable($khoaHocTable)) {
            if (Schema::hasColumn($khoaHocTable, 'nam')) {
                try {
                    $years = KhoaHoc::query()
                        ->whereNotNull('nam')
                        ->distinct()
                        ->orderByDesc('nam')
                        ->pluck('nam');
                } catch (\Throwable) {
                    $years = collect();
                }
            }

            if ($years->isEmpty()) {
                $dateColumn = Schema::hasColumn($khoaHocTable, 'ngay_bat_dau')
                    ? 'ngay_bat_dau'
                    : (Schema::hasColumn($khoaHocTable, 'created_at') ? 'created_at' : null);

                if ($dateColumn) {
                    try {
                        $years = DB::table($khoaHocTable)
                            ->whereNotNull($dateColumn)
                            ->selectRaw("DISTINCT YEAR({$dateColumn}) as y")
                            ->orderByDesc('y')
                            ->pluck('y');
                    } catch (\Throwable) {
                        $years = collect();
                    }
                }
            }
        }

        if ($years->isEmpty()) {
            $dangKyTable = (new DangKy())->getTable();
            $dkCol = 'thoi_gian_dao_tao';

            if (Schema::hasTable($dangKyTable) && Schema::hasColumn($dangKyTable, $dkCol)) {
                try {
                    $years = DB::table($dangKyTable)
                        ->whereNotNull($dkCol)
                        ->selectRaw("DISTINCT IF(LENGTH({$dkCol})=4, {$dkCol}, YEAR({$dkCol})) as y")
                        ->orderByDesc('y')
                        ->pluck('y');
                } catch (\Throwable) {
                    $years = collect();
                }
            } elseif (Schema::hasTable('lich_hocs')) {
                if (Schema::hasColumn('lich_hocs', 'nam')) {
                    $years = DB::table('lich_hocs')
                        ->whereNotNull('nam')
                        ->distinct()
                        ->orderByDesc('nam')
                        ->pluck('nam');
                } elseif (Schema::hasColumn('lich_hocs', 'ngay_hoc')) {
                    $years = DB::table('lich_hocs')
                        ->whereNotNull('ngay_hoc')
                        ->selectRaw('DISTINCT YEAR(ngay_hoc) as y')
                        ->orderByDesc('y')
                        ->pluck('y');
                }
            }
        }

        $now = now()->year;
        if ($years->isEmpty()) $years = collect([$now]);
        elseif (!$years->contains($now)) $years = $years->prepend($now)->sortDesc();

        return $this->planYearCache = $years
            ->map(fn ($v) => filter_var($v, FILTER_VALIDATE_INT))
            ->filter(fn ($v) => $v !== false && $v > 1900)
            ->unique()
            ->values();
    }

    // ===== Course map theo tháng (áp dụng lọc loại hình) =====
    private function courseIdsByMonth(int $year): array
    {
        $selected = $this->getSelectedTrainingTypes();
        sort($selected);
        $cacheKey = $year . '_' . implode('-', $selected);

        if (isset($this->courseMonthCache[$cacheKey])) {
            return $this->courseMonthCache[$cacheKey];
        }

        $buckets = array_fill(1, 12, []);
        if (!Schema::hasTable('lich_hocs')) {
            return $this->courseMonthCache[$cacheKey] = $buckets;
        }

        $q = DB::table('lich_hocs')->select(['khoa_hoc_id', 'thang', 'ngay_hoc'])->whereNotNull('khoa_hoc_id');
        if (Schema::hasColumn('lich_hocs', 'nam'))      $q->where('nam', $year);
        elseif (Schema::hasColumn('lich_hocs', 'ngay_hoc')) $q->whereYear('ngay_hoc', $year);
        else return $this->courseMonthCache[$cacheKey] = $buckets;

        $rows = $q->get();
        foreach ($rows as $r) {
            $month = (int) ($r->thang ?? 0);
            if ($month < 1 || $month > 12) {
                try { $month = $r->ngay_hoc ? (int) Carbon::parse($r->ngay_hoc)->month : 0; } catch (\Throwable) { $month = 0; }
            }
            if ($month < 1 || $month > 12) continue;

            $cid = (int) $r->khoa_hoc_id;
            if ($cid <= 0) continue;

            $buckets[$month][$cid] = true;
        }
        foreach ($buckets as $m => $ids) $buckets[$m] = array_keys($ids);

        // Không chọn loại hình => giữ nguyên
        if (empty($selected)) {
            return $this->courseMonthCache[$cacheKey] = $buckets;
        }

        // Lọc course theo loại hình đã chọn
        $allCourseIds = collect($buckets)->flatten()->unique()->values()->all();
        if (empty($allCourseIds)) {
            return $this->courseMonthCache[$cacheKey] = $buckets;
        }

        $allowed = $this->allowedCourseIdsForSelectedTypes($selected, $allCourseIds);
        if ($allowed === null) {
            // không xác định mapping => không lọc
            return $this->courseMonthCache[$cacheKey] = $buckets;
        }

        $allowedSet = array_fill_keys($allowed, true);
        foreach ($buckets as $m => $ids) {
            $buckets[$m] = array_values(array_filter($ids, fn ($id) => isset($allowedSet[$id])));
        }

        return $this->courseMonthCache[$cacheKey] = $buckets;
    }

    /**
     * Trả về danh sách course_id được phép theo selected types; null nếu không xác định được mapping.
     */
    private function allowedCourseIdsForSelectedTypes(array $selectedTypes, array $limitCourseIds): ?array
    {
        $khTable = (new KhoaHoc())->getTable();
        $dkTable = (new DangKy())->getTable();

        // 1) Theo KhoaHoc.loai_hinh_dao_tao
        if (Schema::hasTable($khTable) && Schema::hasColumn($khTable, 'loai_hinh_dao_tao')) {
            $ids = DB::table($khTable)
                ->whereIn('id', $limitCourseIds)
                ->whereIn('loai_hinh_dao_tao', $selectedTypes)
                ->pluck('id')
                ->all();
            if (!empty($ids)) return $ids;
        }

        // 2) Theo DangKy.loai_hinh_dao_tao
        if (Schema::hasTable($dkTable) && Schema::hasColumn($dkTable, 'loai_hinh_dao_tao')) {
            $ids = DB::table($dkTable)
                ->whereIn('khoa_hoc_id', $limitCourseIds)
                ->whereIn('loai_hinh_dao_tao', $selectedTypes)
                ->distinct()
                ->pluck('khoa_hoc_id')
                ->all();
            if (!empty($ids)) return $ids;
        }

        // 3) Suy luận từ mã (prefix/regex)
        $rules = $this->loadCodeRuleMap();
        $codeColumnsDK  = $this->getPotentialCodeColumns($dkTable);
        $codeColumnsKH  = $this->getPotentialCodeColumns($khTable);
        $allowed = [];

        if (Schema::hasTable($dkTable) && !empty($codeColumnsDK)) {
            foreach ($codeColumnsDK as $col) {
                if (!Schema::hasColumn($dkTable, $col)) continue;
                $rows = DB::table($dkTable)
                    ->select(['khoa_hoc_id', $col])
                    ->whereIn('khoa_hoc_id', $limitCourseIds)
                    ->whereNotNull($col)
                    ->get();
                foreach ($rows as $r) {
                    $type = $this->guessTypeFromCode((string) $r->{$col}, $rules);
                    if ($type !== null && in_array($type, $selectedTypes, true)) {
                        $allowed[(int) $r->khoa_hoc_id] = true;
                    }
                }
            }
        }

        if (empty($allowed) && Schema::hasTable($khTable) && !empty($codeColumnsKH)) {
            foreach ($codeColumnsKH as $col) {
                if (!Schema::hasColumn($khTable, $col)) continue;
                $rows = DB::table($khTable)
                    ->select(['id', $col])
                    ->whereIn('id', $limitCourseIds)
                    ->whereNotNull($col)
                    ->get();
                foreach ($rows as $r) {
                    $type = $this->guessTypeFromCode((string) $r->{$col}, $rules);
                    if ($type !== null && in_array($type, $selectedTypes, true)) {
                        $allowed[(int) $r->id] = true;
                    }
                }
            }
        }

        if (!empty($allowed)) return array_keys($allowed);

        return null; // không xác định
    }

    private function loadCodeRuleMap(): array
    {
        $candidates = [
            'quy_tac_ma_khoa',
            'quy_tac_ma_khoas',
            'quy_tac_ma_khoa_hocs',
            'quy_tac_khoa',
            'quy_tac_ma_khoa_rules',
        ];

        $rules = [];
        foreach ($candidates as $table) {
            if (!Schema::hasTable($table)) continue;

            $typeCol = null;
            foreach (['loai_hinh', 'loai_hinh_dao_tao', 'ten_loai', 'ten', 'type'] as $tc) {
                if (Schema::hasColumn($table, $tc)) { $typeCol = $tc; break; }
            }
            if (!$typeCol) continue;

            $hasPrefix = Schema::hasColumn($table, 'prefix');
            $hasRegex  = Schema::hasColumn($table, 'regex') || Schema::hasColumn($table, 'pattern');

            $query = DB::table($table)->select([$typeCol]);
            if ($hasPrefix) $query->addSelect('prefix');
            if (Schema::hasColumn($table, 'regex')) $query->addSelect('regex');
            elseif (Schema::hasColumn($table, 'pattern')) $query->addSelect(DB::raw('pattern as regex'));

            $rows = $query->get();
            foreach ($rows as $r) {
                $type = trim((string) ($r->{$typeCol} ?? ''));
                if ($type === '') continue;
                $rule = ['type' => $type];
                if ($hasPrefix && isset($r->prefix) && $r->prefix !== '') $rule['prefix'] = (string) $r->prefix;
                if (isset($r->regex) && $r->regex !== '') $rule['regex'] = (string) $r->regex;
                $rules[] = $rule;
            }
        }
        return $rules;
    }

    private function guessTypeFromCode(string $code, array $rules): ?string
    {
        $codeTrim = trim($code);
        if ($codeTrim === '') return null;

        // regex trước
        foreach ($rules as $r) {
            if (!empty($r['regex'])) {
                try {
                    if (@preg_match($r['regex'], '') !== false) {
                        if (preg_match($r['regex'], $codeTrim)) return $this->formatTrainingTypeLabel((string) $r['type']);
                    }
                } catch (\Throwable) {}
            }
        }
        // rồi prefix
        foreach ($rules as $r) {
            if (!empty($r['prefix'])) {
                if (str_starts_with(mb_strtoupper($codeTrim), mb_strtoupper((string) $r['prefix']))) {
                    return $this->formatTrainingTypeLabel((string) $r['type']);
                }
            }
        }
        // fallback: tiền tố in hoa liên tiếp
        if (preg_match('/^([A-Z]{2,})\b/u', strtoupper($codeTrim), $m)) {
            return $this->formatTrainingTypeLabel($m[1]);
        }
        return null;
    }

    private function getPotentialCodeColumns(string $table): array
    {
        $cands = ['ma_khoa', 'ma_khoa_hoc', 'ma_khoa_dao_tao', 'ma_lop', 'ma', 'code', 'ma_khoahoc'];
        $exists = [];
        foreach ($cands as $c) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $c)) $exists[] = $c;
        }
        return $exists;
    }

    // ===== Helpers: UI =====
    private function makeBarDataset(string $label, array $data, string $key, array $extra = []): array
    {
        $palette = [
            'dang-ky'          => [59,130,246],
            'hoan-thanh'       => [16,185,129],
            'khong-hoan-thanh' => [249,115,22],
            'summary'          => [107,114,128],
        ];
        $rgb = $palette[$key] ?? [107,114,128];

        $base = [
            'label' => $label,
            'data'  => array_values($data),
            'backgroundColor' => sprintf('rgba(%d,%d,%d,0.85)', ...$rgb),
            'borderColor' => sprintf('rgba(%d,%d,%d,1)', ...$rgb),
            'borderWidth' => 1,
            'borderRadius' => 8,
            'maxBarThickness' => 60,
        ];
        return array_replace_recursive($base, $extra);
    }

    private function getSelectedTrainingTypes(): array
    {
        return array_values(array_filter($this->selectedTrainingTypes, fn ($v) => $v !== null && $v !== ''));
    }

    private function formatTrainingTypeLabel(string $label): string
    {
        $value = trim((string) $label);
        if ($value === '') return $label;

        try {
            $clean = preg_replace('/^[Vv✓✔☑✅•\-\/\s]+/u', '', $value) ?? $value;
            $clean = preg_replace('/^[-–—]\s*/u', '', $clean) ?? $clean;
            $clean = preg_replace('/[✓✔☑✅]+/u', '', $clean) ?? $clean;
            $clean = preg_replace('/\b[Vv]\b/u', '', $clean) ?? $clean;
            $clean = preg_replace('/\s{2,}/u', ' ', $clean) ?? $clean;
        } catch (\Throwable) {
            $clean = $value ?? '';
        }

        $normalized = trim($clean, " \t\n\r\0\x0B-–—");
        return $normalized !== '' ? $normalized : $value;
    }
}
