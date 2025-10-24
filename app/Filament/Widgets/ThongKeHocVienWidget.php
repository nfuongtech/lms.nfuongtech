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
    public string $month = 'all';
    /** @var array<int,string> */
    public array $selectedTrainingTypes = [];

    // ===== Chart payload for Alpine (entangle) =====
    /** @var array<string,mixed> */
    public array $chartPayload = [];
    /** @var array<string,mixed> */
    public array $chartOptionsPayload = [];

    // ===== Cache =====
    /** @var array<string, mixed> Cache dữ liệu khóa học theo tháng/loại hình */
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
        if (in_array($property, ['year', 'month', 'selectedTrainingTypes'], true)) {
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

    #[Computed]
    public function monthOptions(): array
    {
        $options = ['all' => 'Tất cả'];

        foreach (range(1, 12) as $month) {
            $options[(string) $month] = sprintf('Tháng %02d', $month);
        }

        return $options;
    }

    #[Computed]
    public function selectedMonth(): ?int
    {
        if (is_numeric($this->month)) {
            $month = (int) $this->month;
            if ($month >= 1 && $month <= 12) {
                return $month;
            }
        }

        return null;
    }

    /**
     * Lấy options Loại hình đào tạo từ nhiều nguồn (ưu tiên KhoaHoc, HV Hoàn thành, Đăng ký;
     * nếu thiếu thì suy luận từ mã khóa bằng bảng quy tắc mã/prefix/regex hoặc tiền tố mã).
     */
    #[Computed]
    public function trainingTypeOptions(): array
    {
        $year = $this->year ? (int) $this->year : null;
        $types = collect();

        $khoaHocTable = (new KhoaHoc())->getTable();

        if (Schema::hasTable($khoaHocTable) && Schema::hasColumn($khoaHocTable, 'chuong_trinh_id')) {
            $types = DB::table($khoaHocTable)
                ->join('chuong_trinhs', 'chuong_trinhs.id', '=', 'khoa_hocs.chuong_trinh_id')
                ->when($year !== null && Schema::hasColumn($khoaHocTable, 'nam'), fn ($query) => $query->where('khoa_hocs.nam', $year))
                ->whereNotNull('chuong_trinhs.loai_hinh_dao_tao')
                ->where('chuong_trinhs.loai_hinh_dao_tao', '!=', '')
                ->distinct()
                ->orderBy('chuong_trinhs.loai_hinh_dao_tao')
                ->pluck('chuong_trinhs.loai_hinh_dao_tao');
        }

        if ($types->isEmpty()) {
            $fallbackSources = [
                (new DangKy())->getTable(),
                (new HocVienHoanThanh())->getTable(),
            ];

            foreach ($fallbackSources as $table) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'loai_hinh_dao_tao')) {
                    continue;
                }

                $types = $types->merge(
                    DB::table($table)
                        ->when($year !== null && Schema::hasColumn($table, 'nam'), fn ($query) => $query->where('nam', $year))
                        ->whereNotNull('loai_hinh_dao_tao')
                        ->where('loai_hinh_dao_tao', '!=', '')
                        ->distinct()
                        ->pluck('loai_hinh_dao_tao')
                );
            }
        }

        $types = $types
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return $types
            ->mapWithKeys(fn ($value) => [$value => $this->formatTrainingTypeLabel($value)])
            ->all();
    }

    // ===== Computed: Table & Chart =====
    #[Computed]
    public function monthlySummaryTableData(): array
    {
        if ($this->year === null) {
            return [
                'rows' => [],
                'summary' => [
                    'perMonth' => $this->emptyMonthlyStatusBuckets(),
                    'total' => ['dk' => 0, 'ht' => 0, 'kht' => 0],
                ],
                'hasData' => false,
            ];
        }

        $year = (int) $this->year;
        $selectedTypes = $this->getSelectedTrainingTypes();
        $availableTypes = array_keys($this->trainingTypeOptions);
        $types = !empty($selectedTypes) ? $selectedTypes : $availableTypes;

        if (empty($types)) {
            return [
                'rows' => [],
                'summary' => [
                    'perMonth' => $this->emptyMonthlyStatusBuckets(),
                    'total' => ['dk' => 0, 'ht' => 0, 'kht' => 0],
                ],
                'hasData' => false,
            ];
        }

        $courseMaps = $this->courseIdsByMonthForTypes($year, $types);
        $rows = [];
        $summaryPerMonth = $this->emptyMonthlyStatusBuckets();
        $summaryTotals = ['dk' => 0, 'ht' => 0, 'kht' => 0];
        $khtTableExists = Schema::hasTable((new HocVienKhongHoanThanh())->getTable());

        foreach ($types as $type) {
            $courseMap = $courseMaps[$type] ?? $this->emptyMonthlyCourseBuckets();

            $dangKyCounts = $this->countByMonthUsingCourseMap(DangKy::query(), $courseMap);
            $hoanThanhCounts = $this->countByMonthUsingCourseMap(HocVienHoanThanh::query(), $courseMap);
            $khongHoanThanhCounts = $khtTableExists
                ? $this->countByMonthUsingCourseMap(HocVienKhongHoanThanh::query(), $courseMap)
                : array_fill(1, 12, 0);

            $monthly = [];
            $totals = ['dk' => 0, 'ht' => 0, 'kht' => 0];

            foreach (range(1, 12) as $month) {
                $dk = (int) ($dangKyCounts[$month] ?? 0);
                $ht = (int) ($hoanThanhCounts[$month] ?? 0);
                $kht = (int) ($khongHoanThanhCounts[$month] ?? 0);

                if (!$khtTableExists) {
                    $kht = max(0, $dk - $ht);
                }

                $monthly[$month] = ['dk' => $dk, 'ht' => $ht, 'kht' => $kht];

                $totals['dk'] += $dk;
                $totals['ht'] += $ht;
                $totals['kht'] += $kht;

                $summaryPerMonth[$month]['dk'] += $dk;
                $summaryPerMonth[$month]['ht'] += $ht;
                $summaryPerMonth[$month]['kht'] += $kht;
            }

            $summaryTotals['dk'] += $totals['dk'];
            $summaryTotals['ht'] += $totals['ht'];
            $summaryTotals['kht'] += $totals['kht'];

            $rows[] = [
                'type' => $type,
                'label' => $this->trainingTypeOptions[$type] ?? $this->formatTrainingTypeLabel($type),
                'monthly' => $monthly,
                'total' => $totals,
            ];
        }

        $hasData = ($summaryTotals['dk'] + $summaryTotals['ht'] + $summaryTotals['kht']) > 0;

        $selectedMonth = $this->selectedMonth;

        if ($selectedMonth !== null) {
            $summaryTotals = [
                'dk' => $summaryPerMonth[$selectedMonth]['dk'] ?? 0,
                'ht' => $summaryPerMonth[$selectedMonth]['ht'] ?? 0,
                'kht' => $summaryPerMonth[$selectedMonth]['kht'] ?? 0,
            ];

            foreach ($summaryPerMonth as $month => &$bucket) {
                if ($month !== $selectedMonth) {
                    $bucket = ['dk' => 0, 'ht' => 0, 'kht' => 0];
                }
            }
            unset($bucket);

            foreach ($rows as &$row) {
                foreach ($row['monthly'] as $month => &$bucket) {
                    if ($month !== $selectedMonth) {
                        $bucket = ['dk' => 0, 'ht' => 0, 'kht' => 0];
                    }
                }
                unset($bucket);

                $row['total'] = $row['monthly'][$selectedMonth] ?? ['dk' => 0, 'ht' => 0, 'kht' => 0];
            }
            unset($row);

            $hasData = ($summaryTotals['dk'] + $summaryTotals['ht'] + $summaryTotals['kht']) > 0;
        }

        return [
            'rows' => $rows,
            'summary' => [
                'perMonth' => $summaryPerMonth,
                'total' => $summaryTotals,
            ],
            'hasData' => $hasData,
        ];
    }

    #[Computed]
    public function chartData(): array
    {
        $summary = $this->monthlySummaryTableData['summary']['perMonth'] ?? [];
        $months = range(1, 12);

        $dkSeries = [];
        $htSeries = [];
        $khtSeries = [];

        foreach ($months as $month) {
            $bucket = $summary[$month] ?? ['dk' => 0, 'ht' => 0, 'kht' => 0];
            $dkSeries[] = (int) ($bucket['dk'] ?? 0);
            $htSeries[] = (int) ($bucket['ht'] ?? 0);
            $khtSeries[] = (int) ($bucket['kht'] ?? 0);
        }

        return [
            'labels'   => collect($months)->map(fn ($i) => sprintf('T%02d', $i))->all(),
            'datasets' => [
                $this->makeBarDataset('ĐK', $dkSeries, 'dang-ky'),
                $this->makeBarDataset('HT', $htSeries, 'hoan-thanh'),
                $this->makeBarDataset('Không hoàn thành', $khtSeries, 'khong-hoan-thanh'),
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
                    'maxBarThickness' => 36,
                    'categoryPercentage' => 0.7,
                    'barPercentage' => 0.8,
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
        if ($this->planYearCache !== null) {
            return $this->planYearCache;
        }

        $years = collect();
        $khoaHocTable = (new KhoaHoc())->getTable();

        if (Schema::hasTable($khoaHocTable)) {
            if (Schema::hasColumn($khoaHocTable, 'nam')) {
                $years = DB::table($khoaHocTable)
                    ->whereNotNull('nam')
                    ->selectRaw('DISTINCT nam as y')
                    ->orderByDesc('y')
                    ->pluck('y');
            } elseif (Schema::hasColumn($khoaHocTable, 'ngay_bat_dau')) {
                $years = DB::table($khoaHocTable)
                    ->whereNotNull('ngay_bat_dau')
                    ->selectRaw('DISTINCT YEAR(ngay_bat_dau) as y')
                    ->orderByDesc('y')
                    ->pluck('y');
            } elseif (Schema::hasColumn($khoaHocTable, 'created_at')) {
                $years = DB::table($khoaHocTable)
                    ->whereNotNull('created_at')
                    ->selectRaw('DISTINCT YEAR(created_at) as y')
                    ->orderByDesc('y')
                    ->pluck('y');
            }
        }

        if ($years->isEmpty() && Schema::hasTable('lich_hocs')) {
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

        if ($years->isEmpty()) {
            $dangKyTable = (new DangKy())->getTable();
            if (Schema::hasTable($dangKyTable)) {
                if (Schema::hasColumn($dangKyTable, 'thoi_gian_dao_tao')) {
                    $years = DB::table($dangKyTable)
                        ->whereNotNull('thoi_gian_dao_tao')
                        ->selectRaw('DISTINCT IF(LENGTH(thoi_gian_dao_tao)=4, thoi_gian_dao_tao, YEAR(thoi_gian_dao_tao)) as y')
                        ->orderByDesc('y')
                        ->pluck('y');
                } elseif (Schema::hasColumn($dangKyTable, 'created_at')) {
                    $years = DB::table($dangKyTable)
                        ->whereNotNull('created_at')
                        ->selectRaw('DISTINCT YEAR(created_at) as y')
                        ->orderByDesc('y')
                        ->pluck('y');
                }
            }
        }

        $now = now()->year;

        if ($years->isEmpty()) {
            $years = collect([$now]);
        } elseif (! $years->contains($now)) {
            $years = $years->prepend($now)->sortDesc();
        }

        return $this->planYearCache = $years
            ->map(fn ($value) => filter_var($value, FILTER_VALIDATE_INT))
            ->filter(fn ($value) => $value !== false && $value > 1900)
            ->unique()
            ->values();
    }

    // ===== Course map theo tháng (áp dụng lọc loại hình) =====
    private function courseIdsByMonthForTypes(int $year, array $types): array
    {
        $normalized = array_values(array_filter(
            array_map(fn ($value) => trim((string) $value), $types),
            fn ($value) => $value !== ''
        ));

        sort($normalized);

        $cacheKey = 'types:' . $year . ':' . md5(json_encode($normalized));
        if (isset($this->courseMonthCache[$cacheKey])) {
            return $this->courseMonthCache[$cacheKey];
        }

        if (! Schema::hasTable('lich_hocs') || ! Schema::hasTable('khoa_hocs')) {
            return $this->courseMonthCache[$cacheKey] = [];
        }

        $baseTypes = ! empty($normalized) ? $normalized : array_keys($this->trainingTypeOptions);
        $result = [];

        foreach ($baseTypes as $type) {
            $result[$type] = $this->emptyMonthlyCourseBuckets();
        }

        if (empty($baseTypes)) {
            return $this->courseMonthCache[$cacheKey] = [];
        }

        $query = DB::table('lich_hocs')
            ->join('khoa_hocs', 'khoa_hocs.id', '=', 'lich_hocs.khoa_hoc_id')
            ->leftJoin('chuong_trinhs', 'chuong_trinhs.id', '=', 'khoa_hocs.chuong_trinh_id')
            ->select([
                'lich_hocs.khoa_hoc_id',
                'lich_hocs.thang',
                'lich_hocs.ngay_hoc',
                'chuong_trinhs.loai_hinh_dao_tao',
            ])
            ->whereNotNull('lich_hocs.khoa_hoc_id')
            ->whereNotNull('chuong_trinhs.loai_hinh_dao_tao')
            ->where('chuong_trinhs.loai_hinh_dao_tao', '!=', '');

        if (Schema::hasColumn('khoa_hocs', 'nam')) {
            $query->where('khoa_hocs.nam', $year);
        } elseif (Schema::hasColumn('lich_hocs', 'nam')) {
            $query->where('lich_hocs.nam', $year);
        } elseif (Schema::hasColumn('lich_hocs', 'ngay_hoc')) {
            $query->whereYear('lich_hocs.ngay_hoc', $year);
        } else {
            return $this->courseMonthCache[$cacheKey] = [];
        }

        if (! empty($normalized)) {
            $query->whereIn('chuong_trinhs.loai_hinh_dao_tao', $normalized);
        }

        $rows = $query->get();

        foreach ($rows as $row) {
            $type = trim((string) ($row->loai_hinh_dao_tao ?? ''));
            if ($type === '') {
                continue;
            }

            if (! isset($result[$type])) {
                if (! empty($normalized) && ! in_array($type, $normalized, true)) {
                    continue;
                }

                $result[$type] = $this->emptyMonthlyCourseBuckets();
            }

            $month = (int) ($row->thang ?? 0);
            if ($month < 1 || $month > 12) {
                $month = $this->resolveMonthFromDate($row->ngay_hoc ?? null);
            }
            if ($month < 1 || $month > 12) {
                continue;
            }

            $courseId = (int) ($row->khoa_hoc_id ?? 0);
            if ($courseId <= 0) {
                continue;
            }

            $result[$type][$month][$courseId] = true;
        }

        foreach ($result as $type => $months) {
            foreach ($months as $month => $ids) {
                $result[$type][$month] = array_keys($ids);
            }
        }

        return $this->courseMonthCache[$cacheKey] = $result;
    }

    private function resolveMonthFromDate($value): int
    {
        if (empty($value)) {
            return 0;
        }

        try {
            return (int) Carbon::parse($value)->month;
        } catch (\Throwable) {
            return 0;
        }
    }

    private function emptyMonthlyStatusBuckets(): array
    {
        return array_fill(1, 12, ['dk' => 0, 'ht' => 0, 'kht' => 0]);
    }

    private function emptyMonthlyCourseBuckets(): array
    {
        return array_fill(1, 12, []);
    }

    private function countByMonthUsingCourseMap(Builder $query, array $courseMap): array
    {
        $buckets = array_fill(1, 12, 0);

        $allCourseIds = collect($courseMap)->flatten()->unique()->values()->all();
        if (empty($allCourseIds)) {
            return $buckets;
        }

        $table = $query->getModel()->getTable();
        $keyName = $query->getModel()->getQualifiedKeyName();

        try {
            $rows = $query
                ->whereIn("{$table}.khoa_hoc_id", $allCourseIds)
                ->selectRaw("{$table}.khoa_hoc_id as course_id, COUNT(DISTINCT {$keyName}) as aggregate")
                ->groupBy("{$table}.khoa_hoc_id")
                ->pluck('aggregate', 'course_id');

            foreach ($courseMap as $month => $idsInMonth) {
                $total = 0;
                foreach ($idsInMonth as $courseId) {
                    $total += (int) ($rows[$courseId] ?? 0);
                }
                if (isset($buckets[$month])) {
                    $buckets[$month] = $total;
                }
            }
        } catch (\Throwable) {
            return array_fill(1, 12, 0);
        }

        return $buckets;
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
            'maxBarThickness' => 36,
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
