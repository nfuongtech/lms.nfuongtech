<?php

namespace App\Filament\Widgets;

use App\Models\DangKy;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use App\Models\KhoaHoc;
use Filament\Forms;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ThongKeHocVienChart extends ChartWidget
{
    protected static ?string $heading = 'Thống kê Học viên theo tháng';
    protected static ?string $maxHeight = '380px';
    protected int|string|array $columnSpan = ['md' => 12, 'xl' => 6];

    /**
     * @var Collection|null
     */
    protected $planYearCache = null;

    /**
     * Cache of course ids grouped by month for a given training plan year.
     *
     * @var array<int, array<int, int[]>>
     */
    protected $courseMonthCache = [];

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('year')
                ->label('Năm')
                ->options($this->getPlanYearOptions())
                ->default($this->getDefaultYear())
                ->live(),

            Forms\Components\Select::make('month')
                ->label('Tháng')
                ->placeholder('Tất cả các tháng')
                ->options(collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => sprintf('T%02d', $m)])->all())
                ->default(null)
                ->live(),
        ];
    }

    protected function getData(): array
    {
        $year  = (int) ($this->filterFormData['year'] ?? $this->getDefaultYear());
        $month = $this->filterFormData['month'] ?? null;
        $month = ($month === '' || $month === null) ? null : (int) $month;

        $monthlySeries = $this->compileMonthlySeries($year);

        if ($month) {
            $reg  = $monthlySeries['dangKy'][$month] ?? 0;
            $done = $monthlySeries['hoanThanh'][$month] ?? 0;

            [$_totalNotDone, $vangP, $vangKP, $vangKhac] = $this->countKhongHoanThanhWithAbsence($year, $month);

            $datasets = [
                $this->makeBarDataset('Đăng ký', [$reg], 'dang-ky', ['stack' => 'dang-ky']),
                $this->makeBarDataset('Hoàn thành', [$done], 'hoan-thanh', ['stack' => 'hoan-thanh']),
                $this->makeBarDataset('Không hoàn thành - Vắng P', [$vangP], 'vang-p', ['stack' => 'khong-hoan-thanh']),
                $this->makeBarDataset('Không hoàn thành - Vắng KP', [$vangKP], 'vang-kp', ['stack' => 'khong-hoan-thanh']),
                $this->makeBarDataset('Không hoàn thành - Khác', [$vangKhac], 'vang-khac', ['stack' => 'khong-hoan-thanh']),
            ];

            return [
                'datasets' => $datasets,
                'labels' => [sprintf('T%02d/%d', $month, $year)],
            ];
        }

        $labels   = collect(range(1, 12))->map(fn ($m) => sprintf('T%02d', $m))->all();
        $datasets = [
            $this->makeBarDataset('Đăng ký', array_values($monthlySeries['dangKy']), 'dang-ky'),
            $this->makeBarDataset('Hoàn thành', array_values($monthlySeries['hoanThanh']), 'hoan-thanh'),
            $this->makeBarDataset('Không hoàn thành', array_values($monthlySeries['khongHoanThanh']), 'khong-hoan-thanh'),
        ];

        return [
            'datasets' => $datasets,
            'labels'  => $labels,
        ];
    }

    protected function getOptions(): array
    {
        $detail = ! empty($this->filterFormData['month']);
        $tooltipCallbacks = [
            'label' => [
                'type' => 'dataset-value',
                'axis' => 'y',
                'locale' => 'vi-VN',
            ],
        ];

        if ($detail) {
            $tooltipCallbacks['footer'] = [
                'type' => 'stacked-sum',
                'stack' => 'khong-hoan-thanh',
                'label' => 'Tổng không hoàn thành',
                'locale' => 'vi-VN',
            ];
        }

        return [
            'animation' => [ 'duration' => 900, 'easing' => 'easeOutQuart' ],
            'plugins'   => [
                'legend'  => [ 'position' => 'top', 'labels' => [ 'usePointStyle' => true ]],
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false,
                    'callbacks' => $tooltipCallbacks,
                ],
                'barValueLabels' => [
                    'padding' => 6,
                    'color' => '#111827',
                    'font' => [
                        'size' => 11,
                        'weight' => '600',
                    ],
                    'showZero' => true,
                    'locale' => 'vi-VN',
                    'formatter' => [
                        'type' => 'number',
                        'locale' => 'vi-VN',
                        'maximumFractionDigits' => 0,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'layout' => [
                'padding' => [ 'top' => 24, 'right' => 16, 'left' => 8 ],
            ],
            'interaction' => [ 'mode' => 'index', 'intersect' => false ],
            'scales' => [
                'x' => [
                    'stacked' => (bool) $detail,
                    'ticks'   => [ 'font' => [ 'size' => 12 ]],
                    'grid'    => [ 'display' => false ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => [ 'font' => [ 'size' => 12 ]],
                    'grid'        => [ 'drawBorder' => false ],
                ],
            ],
        ];
    }

    private function makeBarDataset(string $label, array $data, string $colorKey, array $overrides = []): array
    {
        $color = $this->colorForKey($colorKey);

        return array_merge([
            'label' => $label,
            'data' => $data,
            'backgroundColor' => $color['background'],
            'hoverBackgroundColor' => $color['border'],
            'borderColor' => $color['border'],
            'borderWidth' => 1,
            'borderRadius' => 12,
            'borderSkipped' => false,
            'maxBarThickness' => 40,
            'categoryPercentage' => 0.72,
            'barPercentage' => 0.85,
        ], $overrides);
    }

    private function colorForKey(string $key): array
    {
        $palette = [
            'dang-ky'           => [59, 130, 246],
            'hoan-thanh'        => [16, 185, 129],
            'khong-hoan-thanh'  => [249, 115, 22],
            'vang-p'            => [251, 191, 36],
            'vang-kp'           => [239, 68, 68],
            'vang-khac'         => [129, 140, 248],
        ];

        $rgb = $palette[$key] ?? [107, 114, 128];

        return [
            'background' => sprintf('rgba(%d, %d, %d, 0.85)', $rgb[0], $rgb[1], $rgb[2]),
            'border' => sprintf('rgba(%d, %d, %d, 1)', $rgb[0], $rgb[1], $rgb[2]),
        ];
    }

    private function getDefaultYear(): int
    {
        return (int) ($this->planYears()->first() ?? now()->year);
    }

    private function getPlanYearOptions(): array
    {
        return $this->planYears()
            ->mapWithKeys(fn ($year) => [$year => (string) $year])
            ->all();
    }

    private function planYears(): Collection
    {
        if ($this->planYearCache !== null) {
            return $this->planYearCache;
        }

        $years = collect();
        $khoaHocTable = (new KhoaHoc)->getTable();

        if (Schema::hasTable($khoaHocTable) && Schema::hasColumn($khoaHocTable, 'nam')) {
            $years = KhoaHoc::query()
                ->whereNotNull('nam')
                ->distinct()
                ->orderByDesc('nam')
                ->pluck('nam');
        }

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return $this->planYearCache = $years
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->unique()
            ->values();
    }

    private function compileMonthlySeries(int $year): array
    {
        return [
            'dangKy' => $this->monthlyDangKyCounts($year),
            'hoanThanh' => $this->monthlyHoanThanhCounts($year),
            'khongHoanThanh' => $this->monthlyKhongHoanThanhCounts($year),
        ];
    }

    private function monthlyDangKyCounts(int $year): array
    {
        return $this->countByMonthUsingCourseMap(DangKy::query(), $this->courseIdsByMonth($year));
    }

    private function monthlyHoanThanhCounts(int $year): array
    {
        return $this->countByMonthUsingCourseMap(HocVienHoanThanh::query(), $this->courseIdsByMonth($year));
    }

    private function monthlyKhongHoanThanhCounts(int $year): array
    {
        return $this->countByMonthUsingCourseMap(HocVienKhongHoanThanh::query(), $this->courseIdsByMonth($year));
    }

    private function emptyMonthlyBuckets(): array
    {
        $buckets = [];

        for ($month = 1; $month <= 12; $month++) {
            $buckets[$month] = 0;
        }

        return $buckets;
    }

    private function emptyMonthlyCourseBuckets(): array
    {
        $buckets = [];

        for ($month = 1; $month <= 12; $month++) {
            $buckets[$month] = [];
        }

        return $buckets;
    }

    private function countByMonthUsingCourseMap(Builder $query, array $courseMap): array
    {
        $buckets = $this->emptyMonthlyBuckets();
        $courseIds = [];

        foreach ($courseMap as $ids) {
            foreach ($ids as $courseId) {
                $courseIds[$courseId] = true;
            }
        }

        if (empty($courseIds)) {
            return $buckets;
        }

        $table = $query->getModel()->getTable();

        $keyName = $query->getModel()->getQualifiedKeyName();

        $rows = (clone $query)
            ->whereIn("$table.khoa_hoc_id", array_keys($courseIds))
            ->selectRaw("$table.khoa_hoc_id as course_id")
            ->selectRaw('COUNT(DISTINCT ' . $keyName . ') as aggregate')
            ->groupBy('course_id')
            ->pluck('aggregate', 'course_id')
            ->all();

        foreach ($courseMap as $month => $ids) {
            $total = 0;
            foreach ($ids as $courseId) {
                $total += (int) ($rows[$courseId] ?? 0);
            }
            $buckets[$month] = $total;
        }

        return $buckets;
    }

    private function countKhongHoanThanhWithAbsence(int $year, int $month): array
    {
        $courseIds = $this->courseIdsByMonth($year)[$month] ?? [];

        if (empty($courseIds)) {
            return [0, 0, 0, 0];
        }

        $table = (new HocVienKhongHoanThanh)->getTable();
        $query = HocVienKhongHoanThanh::query()->whereIn("$table.khoa_hoc_id", $courseIds);
        $idColumn = "$table.id";

        $total = (clone $query)->distinct($idColumn)->count($idColumn);
        $vangP = 0;
        $vangKP = 0;

        if (Schema::hasColumn($table, 'vang_co_phep')) {
            $vangP  = (clone $query)->where("$table.vang_co_phep", 1)->distinct($idColumn)->count($idColumn);
            $vangKP = (clone $query)->where("$table.vang_co_phep", 0)->distinct($idColumn)->count($idColumn);
        } elseif (Schema::hasColumn($table, 'loai_vang')) {
            $vangP  = (clone $query)->whereIn("$table.loai_vang", ['p', 'phep', 'vang_p', 'Vắng P', 'Vang P'])->distinct($idColumn)->count($idColumn);
            $vangKP = (clone $query)->whereIn("$table.loai_vang", ['kp', 'khong_phep', 'vang_kp', 'Vắng KP', 'Vang KP'])->distinct($idColumn)->count($idColumn);
        } elseif (Schema::hasColumn($table, 'tinh_trang')) {
            $vangP  = (clone $query)->where(DB::raw('LOWER(' . $table . '.tinh_trang)'), 'like', '%p%')->distinct($idColumn)->count($idColumn);
            $vangKP = max($total - $vangP, 0);
        }

        $vangKhac = max($total - $vangP - $vangKP, 0);

        return [$total, $vangP, $vangKP, $vangKhac];
    }

    private function courseIdsByMonth(int $year): array
    {
        if (array_key_exists($year, $this->courseMonthCache)) {
            return $this->courseMonthCache[$year];
        }

        $buckets = $this->emptyMonthlyCourseBuckets();

        if (! Schema::hasTable('lich_hocs')) {
            return $this->courseMonthCache[$year] = $buckets;
        }

        $query = DB::table('lich_hocs')
            ->select(['khoa_hoc_id', 'thang', 'ngay_hoc'])
            ->whereNotNull('khoa_hoc_id');

        if (Schema::hasColumn('lich_hocs', 'nam')) {
            $query->where('nam', $year);
        } else {
            $query->whereYear('ngay_hoc', $year);
        }

        $rows = $query->get();

        foreach ($rows as $row) {
            $month = (int) ($row->thang ?? 0);

            if ($month < 1 || $month > 12) {
                try {
                    $month = $row->ngay_hoc ? (int) Carbon::parse($row->ngay_hoc)->month : 0;
                } catch (\Throwable $e) {
                    $month = 0;
                }
            }

            if ($month < 1 || $month > 12) {
                continue;
            }

            $courseId = (int) $row->khoa_hoc_id;

            if ($courseId <= 0) {
                continue;
            }

            $buckets[$month][$courseId] = true;
        }

        $normalized = [];
        foreach ($buckets as $month => $ids) {
            $normalized[$month] = array_values(array_keys($ids));
        }

        return $this->courseMonthCache[$year] = $normalized;
    }
}
