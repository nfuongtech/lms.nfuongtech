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
    protected int|string|array $columnSpan = ['md' => 12, 'xl' => 12];

    /** UI state */
    public int $year;
    public ?int $month = null;
    /** @var array<int,string> */
    public array $selectedTrainingTypes = [];

    /** cache: [year => [month => int[] courseIds]] */
    protected array $courseMonthCache = [];

    public function mount(): void
    {
        $this->year = $this->defaultYear();
        $this->selectedTrainingTypes = [];
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

    public function getMonthOptionsProperty(): array
    {
        return collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => sprintf('T%02d', $m)])->all();
    }

    public function getTrainingTypeOptionsProperty(): array
    {
        if (! Schema::hasTable('khoa_hocs') || ! Schema::hasColumn('khoa_hocs', 'loai_hinh_dao_tao')) {
            return [];
        }

        return KhoaHoc::query()
            ->whereNotNull('loai_hinh_dao_tao')
            ->where('loai_hinh_dao_tao', '!=', '')
            ->distinct()
            ->orderBy('loai_hinh_dao_tao')
            ->pluck('loai_hinh_dao_tao', 'loai_hinh_dao_tao')
            ->all();
    }

    /** ===== Chart payloads (read in Blade via $this->chartData / chartOptions) ===== */
    public function getChartDataProperty(): array
    {
        $year  = (int) $this->year;
        $month = $this->month ?: null;

        $series = $this->compileMonthlySeries($year);

        if ($month) {
            $reg  = $series['dangKy'][$month] ?? 0;
            $done = $series['hoanThanh'][$month] ?? 0;
            [$_total, $vangP, $vangKP, $vangKhac] = $this->countKhongHoanThanhWithAbsence($year, $month);

            return [
                'labels'   => [sprintf('T%02d/%d', $month, $year)],
                'datasets' => [
                    $this->makeBarDataset('Đăng ký', [$reg], 'dang-ky', ['stack' => 'dang-ky']),
                    $this->makeBarDataset('Hoàn thành', [$done], 'hoan-thanh', ['stack' => 'hoan-thanh']),
                    $this->makeBarDataset('Không hoàn thành - Vắng P', [$vangP], 'vang-p', ['stack' => 'khong-hoan-thanh']),
                    $this->makeBarDataset('Không hoàn thành - Vắng KP', [$vangKP], 'vang-kp', ['stack' => 'khong-hoan-thanh']),
                    $this->makeBarDataset('Không hoàn thành - Khác', [$vangKhac], 'vang-khac', ['stack' => 'khong-hoan-thanh']),
                ],
            ];
        }

        return [
            'labels'   => collect(range(1, 12))->map(fn ($m) => sprintf('T%02d', $m))->all(),
            'datasets' => [
                $this->makeBarDataset('Đăng ký', array_values($series['dangKy']), 'dang-ky'),
                $this->makeBarDataset('Hoàn thành', array_values($series['hoanThanh']), 'hoan-thanh'),
                $this->makeBarDataset('Không hoàn thành', array_values($series['khongHoanThanh']), 'khong-hoan-thanh'),
            ],
        ];
    }

    public function getChartOptionsProperty(): array
    {
        return [
            'plugins' => [
                'legend'  => ['position' => 'top', 'labels' => ['usePointStyle' => true]],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => ['mode' => 'index', 'intersect' => false],
            'layout' => ['padding' => ['top' => 24, 'right' => 16, 'bottom' => 12, 'left' => 8]],
            'scales' => [
                'x' => ['grid' => ['display' => false]],
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
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

    private function emptyMonthlyBuckets(): array      { return array_fill(1, 12, 0); }
    private function emptyMonthlyCourseBuckets(): array{ return array_fill(1, 12, []); }

    private function makeBarDataset(string $label, array $data, string $key, array $extra = []): array
    {
        $base = [
            'label' => $label,
            'data'  => array_values($data),
            'backgroundColor' => match ($key) {
                'dang-ky' => 'rgba(59,130,246,0.80)',
                'hoan-thanh' => 'rgba(34,197,94,0.80)',
                'khong-hoan-thanh' => 'rgba(239,68,68,0.80)',
                'vang-p' => 'rgba(250,204,21,0.85)',
                'vang-kp' => 'rgba(248,113,113,0.85)',
                'vang-khac' => 'rgba(148,163,184,0.85)',
                default => 'rgba(99,102,241,0.80)',
            },
            'borderRadius'    => 6,
            'maxBarThickness' => 44,
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

    private function countKhongHoanThanhWithAbsence(int $year, ?int $month = null): array
    {
        $map = $this->courseIdsByMonth($year);
        $courseIds = $month ? ($map[$month] ?? []) : collect($map)->flatten()->unique()->values()->all();
        if (empty($courseIds)) return [0, 0, 0, 0];

        $table = (new HocVienKhongHoanThanh)->getTable();
        $query = HocVienKhongHoanThanh::query()->whereIn("$table.khoa_hoc_id", $courseIds);
        $idCol = "$table.id";

        $total = (clone $query)->distinct($idCol)->count($idCol);
        $vangP = 0; $vangKP = 0;

        if (Schema::hasColumn($table, 'vang_co_phep')) {
            $vangP  = (clone $query)->where("$table.vang_co_phep", 1)->distinct($idCol)->count($idCol);
            $vangKP = (clone $query)->where("$table.vang_co_phep", 0)->distinct($idCol)->count($idCol);
        } elseif (Schema::hasColumn($table, 'loai_vang')) {
            $vangP  = (clone $query)->whereIn("$table.loai_vang", ['P','p','Vắng P','Vang P'])->distinct($idCol)->count($idCol);
            $vangKP = (clone $query)->whereIn("$table.loai_vang", ['KP','kp','Vắng KP','Vang KP'])->distinct($idCol)->count($idCol);
        } elseif (Schema::hasColumn($table, 'tinh_trang')) {
            $vangP  = (clone $query)->where("$table.tinh_trang", 'like', '%P%')->distinct($idCol)->count($idCol);
            $vangKP = max($total - $vangP, 0);
        }

        $vangKhac = max($total - $vangP - $vangKP, 0);
        return [$total, $vangP, $vangKP, $vangKhac];
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
            $allowedIds = KhoaHoc::query()->whereIn('loai_hinh_dao_tao', $selected)->pluck('id')->all();
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
}
