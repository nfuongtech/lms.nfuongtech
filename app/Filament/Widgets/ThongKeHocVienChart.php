<?php

namespace App\Filament\Widgets;

use App\Models\DangKy;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use App\Models\KhoaHoc;
use Filament\Forms;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ThongKeHocVienChart extends ChartWidget
{
    protected static ?string $heading = 'Thống kê Học viên theo tháng';
    protected static ?string $maxHeight = '380px';
    protected int|string|array $columnSpan = ['md' => 12, 'xl' => 6];

    protected ?Collection $planYearCache = null;

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
                ->default(null)
                ->options(collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => sprintf('%02d', $m)])->all())
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
                'labels' => [sprintf('Tháng %02d/%d', $month, $year)],
            ];
        }

        $labels   = collect(range(1, 12))->map(fn ($m) => sprintf('%02d', $m))->all();
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
        $detail = !empty($this->filterFormData['month']);
        $tooltipCallbacks = [
            'label' => new \Illuminate\Support\Js(<<<'JS'
                (ctx) => `${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('vi-VN')}`
            JS),
        ];

        if ($detail) {
            $tooltipCallbacks['footer'] = new \Illuminate\Support\Js(<<<'JS'
                (items) => {
                    if (!items || !items.length) {
                        return '';
                    }

                    const dataIndex = items[0].dataIndex;
                    const chart = items[0].chart;
                    if (!chart) {
                        return '';
                    }

                    const sum = chart.data.datasets
                        .filter((dataset) => dataset.stack === 'khong-hoan-thanh')
                        .reduce((carry, dataset) => {
                            const value = Array.isArray(dataset.data) ? dataset.data[dataIndex] ?? 0 : 0;
                            return carry + Number(value || 0);
                        }, 0);

                    if (!sum) {
                        return '';
                    }

                    return `Tổng không hoàn thành: ${sum.toLocaleString('vi-VN')}`;
                }
            JS);
        }

        return [
            'animation' => [ 'duration' => 900, 'easing' => 'easeOutQuart' ],
            'plugins'   => [
                'legend'  => [ 'position' => 'top', 'labels' => [ 'usePointStyle' => true ]],
                'tooltip' => [
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
            'dangKy' => $this->aggregateMonthlyCounts(
                DangKy::query(),
                ['ngay_dang_ky', 'created_at', 'updated_at'],
                $year
            ),
            'hoanThanh' => $this->aggregateMonthlyCounts(
                HocVienHoanThanh::query(),
                ['ngay_hoan_thanh', 'created_at', 'updated_at'],
                $year
            ),
            'khongHoanThanh' => $this->aggregateMonthlyCounts(
                HocVienKhongHoanThanh::query(),
                ['ngay_khong_hoan_thanh', 'created_at', 'updated_at'],
                $year
            ),
        ];
    }

    private function emptyMonthlyBuckets(): array
    {
        $buckets = [];

        for ($month = 1; $month <= 12; $month++) {
            $buckets[$month] = 0;
        }

        return $buckets;
    }

    private function countKhongHoanThanhWithAbsence(int $year, int $month): array
    {
        $model = new HocVienKhongHoanThanh();
        $table = $model->getTable();
        $query = $model->newQuery();
        $idColumn = $model->getQualifiedKeyName();
        $dateExpression = $this->buildDateExpression($table, ['ngay_khong_hoan_thanh', 'created_at', 'updated_at']);

        if (! $dateExpression) {
            return [0, 0, 0, 0];
        }

        $notNullCondition = $dateExpression . ' IS NOT NULL';
        $yearCondition = 'YEAR(' . $dateExpression . ') = ?';
        $monthCondition = 'MONTH(' . $dateExpression . ') = ?';

        $baseQuery = (clone $query)
            ->whereRaw($notNullCondition)
            ->whereRaw($yearCondition, [$year])
            ->whereRaw($monthCondition, [$month]);

        $total = (clone $baseQuery)->distinct()->count($idColumn);
        $vangP = 0;
        $vangKP = 0;

        if (Schema::hasColumn($table, 'vang_co_phep')) {
            $vangP  = (clone $baseQuery)->where("$table.vang_co_phep", 1)->distinct()->count($idColumn);
            $vangKP = (clone $baseQuery)->where("$table.vang_co_phep", 0)->distinct()->count($idColumn);
        } elseif (Schema::hasColumn($table, 'loai_vang')) {
            $vangP  = (clone $baseQuery)->whereIn("$table.loai_vang", ['p', 'phep', 'vang_p', 'Vắng P', 'Vang P'])->distinct()->count($idColumn);
            $vangKP = (clone $baseQuery)->whereIn("$table.loai_vang", ['kp', 'khong_phep', 'vang_kp', 'Vắng KP', 'Vang KP'])->distinct()->count($idColumn);
        } elseif (Schema::hasColumn($table, 'tinh_trang')) {
            $vangP  = (clone $baseQuery)->where(DB::raw('LOWER(' . $table . '.tinh_trang)'), 'like', '%p%')->distinct()->count($idColumn);
            $vangKP = max($total - $vangP, 0);
        }

        $vangKhac = max($total - $vangP - $vangKP, 0);

        return [$total, $vangP, $vangKP, $vangKhac];
    }

    private function aggregateMonthlyCounts(Builder $query, array $dateColumns, int $year): array
    {
        $buckets = $this->emptyMonthlyBuckets();

        $table = $query->getModel()->getTable();
        $dateExpression = $this->buildDateExpression($table, $dateColumns);

        if (! $dateExpression) {
            return $buckets;
        }

        $keyName = $query->getModel()->getQualifiedKeyName();

        $rows = (clone $query)
            ->selectRaw('MONTH(' . $dateExpression . ') as month')
            ->selectRaw('COUNT(DISTINCT ' . $keyName . ') as aggregate')
            ->whereRaw($dateExpression . ' IS NOT NULL')
            ->whereRaw('YEAR(' . $dateExpression . ') = ?', [$year])
            ->groupBy('month')
            ->pluck('aggregate', 'month')
            ->all();

        foreach ($rows as $month => $count) {
            $index = (int) $month;

            if ($index < 1 || $index > 12) {
                continue;
            }

            $buckets[$index] = (int) $count;
        }

        return $buckets;
    }

    private function buildDateExpression(string $table, array $candidates): ?string
    {
        if (empty($candidates)) {
            return null;
        }

        $qualified = [];

        foreach ($candidates as $column) {
            if (Schema::hasColumn($table, $column)) {
                $qualified[] = $table . '.' . $column;
            }
        }

        if (empty($qualified)) {
            return null;
        }

        if (count($qualified) === 1) {
            return $qualified[0];
        }

        return 'COALESCE(' . implode(', ', $qualified) . ')';
    }
}
