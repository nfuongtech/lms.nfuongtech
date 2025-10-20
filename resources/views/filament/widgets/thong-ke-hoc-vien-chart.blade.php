<?php

namespace App\Filament\Widgets;

use App\Models\DangKy;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use App\Models\KhoaHoc;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ThongKeHocVienChart extends Widget
{
    protected static string $view = 'filament.widgets.thong-ke-hoc-vien-chart';
    protected int|string|array $columnSpan = 12;
    protected static ?int $sort = 10;

    /** UI state */
    public int $year;
    /** @var array<int,string> */
    public array $selectedTrainingTypes = [];

    /** cache: [year => [month => int[] courseIds]] */
    protected array $courseMonthCache = [];

    public function mount(): void
    {
        $this->year = $this->defaultYear();
        $this->selectedTrainingTypes = [];
    }

    public function updatedYear(): void
    {
        $this->dispatch('$refresh');
    }

    /** Toggle multi-select Loại hình */
    public function toggleTrainingType(string $value): void
    {
        $idx = array_search($value, $this->selectedTrainingTypes, true);
        if ($idx === false) {
            $this->selectedTrainingTypes[] = $value;
        } else {
            array_splice($this->selectedTrainingTypes, $idx, 1);
        }
        $this->dispatch('$refresh');
    }

    public function clearTrainingTypeFilters(): void
    {
        $this->selectedTrainingTypes = [];
        $this->dispatch('$refresh');
    }

    /** ===== Options for Blade ===== */
    public function getYearOptionsProperty(): array
    {
        $years = collect();

        if (Schema::hasTable('lich_hocs')) {
            if (Schema::hasColumn('lich_hocs', 'nam')) {
                $years = $years->merge(
                    DB::table('lich_hocs')->whereNotNull('nam')->distinct()->orderByDesc('nam')->pluck('nam')
                );
            } elseif (Schema::hasColumn('lich_hocs', 'ngay_hoc')) {
                $years = $years->merge(
                    DB::table('lich_hocs')->whereNotNull('ngay_hoc')->selectRaw('DISTINCT YEAR(ngay_hoc) as y')->orderByDesc('y')->pluck('y')
                );
            }
        }

        if ($years->isEmpty() && Schema::hasTable('khoa_hocs')) {
            $col = Schema::hasColumn('khoa_hocs', 'ngay_bat_dau') ? 'ngay_bat_dau' : 'created_at';
            $years = DB::table('khoa_hocs')->whereNotNull($col)->selectRaw("DISTINCT YEAR($col) as y")->orderByDesc('y')->pluck('y');
        }

        if ($years->isEmpty()) $years = collect([(int) now()->format('Y')]);

        return $years->mapWithKeys(fn ($y) => [$y => (string) $y])->all();
    }

    public function getTrainingTypeOptionsProperty(): array
    {
        if (! Schema::hasTable('khoa_hocs') || ! Schema::hasColumn('khoa_hocs', 'loai_hinh_dao_tao')) {
            return [];
        }

        $types = KhoaHoc::query()
            ->whereNotNull('loai_hinh_dao_tao')
            ->where('loai_hinh_dao_tao', '!=', '')
            ->distinct()
            ->orderBy('loai_hinh_dao_tao')
            ->pluck('loai_hinh_dao_tao', 'loai_hinh_dao_tao')
            ->map(fn($label) => $this->formatTrainingTypeLabel($label))
            ->all();

        return $types;
    }

    /** ===== Chart payloads ===== */
    public function getChartDataProperty(): array
    {
        $year = (int) $this->year;
        $series = $this->compileMonthlySeries($year);

        return [
            'labels'   => collect(range(1, 12))->map(fn ($m) => sprintf('T%02d', $m))->all(),
            'datasets' => [
                $this->makeBarDataset('Đăng ký', array_values($series['dangKy']), 'dang-ky'),
                $this->makeBarDataset('Hoàn thành', array_values($series['hoanThanh']), 'hoan-thanh'),
                $this->makeBarDataset('Không hoàn thành', array_values($series['khongHoanThanh']), 'khong-hoan-thanh'),
            ],
        ];
    }

    public function getTotalRegistrations(): int
    {
        $year = (int) $this->year;
        $series = $this->compileMonthlySeries($year);
        return array_sum($series['dangKy']);
    }

    public function getTotalCompleted(): int
    {
        $year = (int) $this->year;
        $series = $this->compileMonthlySeries($year);
        return array_sum($series['hoanThanh']);
    }

    public function getTotalIncomplete(): int
    {
        $year = (int) $this->year;
        $series = $this->compileMonthlySeries($year);
        return array_sum($series['khongHoanThanh']);
    }

    public function getTypeTotalsProperty(): array
    {
        $year = (int) $this->year;
        $typeTotals = [];
        $map = $this->courseIdsByMonth($year);
        $allCourseIds = collect($map)->flatten()->unique()->values()->all();

        if (empty($allCourseIds)) {
            return [];
        }

        $courses = KhoaHoc::whereIn('id', $allCourseIds)
            ->whereNotNull('loai_hinh_dao_tao')
            ->where('loai_hinh_dao_tao', '!=', '')
            ->get();

        foreach ($courses as $course) {
            $type = $this->formatTrainingTypeLabel($course->loai_hinh_dao_tao);
            
            $dangKy = DangKy::where('khoa_hoc_id', $course->id)->count();
            $hoanThanh = HocVienHoanThanh::where('khoa_hoc_id', $course->id)->count();
            $khongHoanThanh = HocVienKhongHoanThanh::where('khoa_hoc_id', $course->id)->count();

            if (!isset($typeTotals[$type])) {
                $typeTotals[$type] = [
                    'dang_ky' => 0,
                    'hoan_thanh' => 0,
                    'khong_hoan_thanh' => 0,
                ];
            }

            $typeTotals[$type]['dang_ky'] += $dangKy;
            $typeTotals[$type]['hoan_thanh'] += $hoanThanh;
            $typeTotals[$type]['khong_hoan_thanh'] += $khongHoanThanh;
        }

        ksort($typeTotals);
        return $typeTotals;
    }

    /** ===== Core ===== */
    private function compileMonthlySeries(int $year): array
    {
        $map = $this->courseIdsByMonth($year);
        return [
            'dangKy'        => $this->countByMonthUsingCourseMap(DangKy::query(), $map),
            'hoanThanh'     => $this->countByMonthUsingCourseMap(HocVienHoanThanh::query(), $map),
            'khongHoanThanh'=> $this->countByMonthUsingCourseMap(HocVienKhongHoanThanh::query(), $map),
        ];
    }

    public function getChartOptionsProperty(): array
    {
        return [
            'plugins' => [
                'legend'  => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                        'boxWidth' => 12,
                        'color' => '#1e293b',
                    ]
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
                        'color' => 'rgba(148, 163, 184, 0.18)',
                        'drawBorder' => false,
                    ],
                ],
            ],
            'datasets' => [
                'bar' => [
                    'borderRadius' => 8,
                    'maxBarThickness' => 46,
                ],
            ],
            'animation' => [
                'duration' => 900,
                'easing' => 'easeOutQuart',
            ],
        ];
    }

    /** ===== Core ===== */
    private function compileMonthlySeries(int $year): array
    {
        $map = $this->courseIdsByMonth($year);
        return [
            'dangKy'        => $this->countByMonthUsingCourseMap(DangKy::query(), $map),
            'hoanThanh'     => $this->countByMonthUsingCourseMap(HocVienHoanThanh::query(), $map),
            'khongHoanThanh'=> $this->countByMonthUsingCourseMap(HocVienKhongHoanThanh::query(), $map),
        ];
    }

    private function emptyMonthlyBuckets(): array { return array_fill(1, 12, 0); }
    private function emptyMonthlyCourseBuckets(): array { return array_fill(1, 12, []); }

    private function makeBarDataset(string $label, array $data, string $key, array $extra = []): array
    {
        $base = [
            'label' => $label,
            'data'  => array_values($data),
            'backgroundColor' => match ($key) {
                'dang-ky' => 'rgba(59,130,246,0.85)',
                'hoan-thanh' => 'rgba(16,185,129,0.85)',
                'khong-hoan-thanh' => 'rgba(239,68,68,0.85)',
                default => 'rgba(99,102,241,0.80)',
            },
            'borderColor' => match ($key) {
                'dang-ky' => 'rgba(37,99,235,1)',
                'hoan-thanh' => 'rgba(5,150,105,1)',
                'khong-hoan-thanh' => 'rgba(220,38,38,1)',
                default => 'rgba(79,70,229,1)',
            },
            'borderWidth' => 1,
            'borderRadius' => 8,
            'maxBarThickness' => 46,
        ];
        return array_replace_recursive($base, $extra);
    }

    private function getSelectedTrainingTypes(): array
    {
        return array_values(array_filter($this->selectedTrainingTypes, fn ($v) => $v !== null && $v !== ''));
    }

    private function countByMonthUsingCourseMap(Builder $query, array $courseMap): array
    {
        $buckets = $this->emptyMonthlyBuckets();
        $courseIds = [];
        foreach ($courseMap as $ids) foreach ($ids as $cid) $courseIds[$cid] = true;
        if (empty($courseIds)) return $buckets;

        $table = $query->getModel()->getTable();
        $pk    = $query->getModel()->getQualifiedKeyName();

        $rows = (clone $query)
            ->whereIn("$table.khoa_hoc_id", array_keys($courseIds))
            ->selectRaw("$table.khoa_hoc_id as course_id")
            ->selectRaw("COUNT(DISTINCT $pk) as aggregate")
            ->groupBy('course_id')
            ->pluck('aggregate', 'course_id')
            ->all();

        foreach ($courseMap as $month => $ids) {
            $sum = 0;
            foreach ($ids as $cid) $sum += (int) ($rows[$cid] ?? 0);
            $buckets[$month] = $sum;
        }
        return $buckets;
    }

    private function courseIdsByMonth(int $year): array
    {
        if (isset($this->courseMonthCache[$year])) return $this->courseMonthCache[$year];

        $buckets = $this->emptyMonthlyCourseBuckets();
        if (! Schema::hasTable('lich_hocs')) return $this->courseMonthCache[$year] = $buckets;

        $q = DB::table('lich_hocs')
            ->select(['khoa_hoc_id', 'thang', 'ngay_hoc'])
            ->whereNotNull('khoa_hoc_id');

        if (Schema::hasColumn('lich_hocs', 'nam')) $q->where('nam', $year);
        else $q->whereYear('ngay_hoc', $year);

        $selected = $this->getSelectedTrainingTypes();
        if (!empty($selected) && Schema::hasTable('khoa_hocs')) {
            $allowedIds = KhoaHoc::query()
                ->whereIn('loai_hinh_dao_tao', $selected)
                ->pluck('id')
                ->all();
            if (!empty($allowedIds)) $q->whereIn('khoa_hoc_id', $allowedIds);
            else return $this->courseMonthCache[$year] = $buckets;
        }

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

        foreach ($buckets as $m => $ids) $buckets[$m] = array_values(array_keys($ids));
        return $this->courseMonthCache[$year] = $buckets;
    }

    private function defaultYear(): int
    {
        $opts = $this->yearOptions;
        $now = (int) now()->format('Y');
        return array_key_exists($now, $opts) ? $now : (int) array_key_first($opts);
    }

    private function formatTrainingTypeLabel(string $label): string
    {
        $value = trim((string) $label);
        $clean = preg_replace('/^[Vv✓✔☑✅•\-\/\s]+/u', '', $value) ?? $value;
        $clean = preg_replace('/^[-–—]\s*/u', '', $clean) ?? $clean;
        $clean = preg_replace('/[✓✔☑✅]+/u', '', $clean) ?? $clean;
        $clean = preg_replace('/\b[Vv]\b/u', '', $clean) ?? $clean;
        $clean = preg_replace('/\s{2,}/u', ' ', $clean) ?? $clean;
        $normalized = trim($clean, " \t\n\r\0\x0B-–—");
        return $normalized !== '' ? $normalized : $value;
    }
}
