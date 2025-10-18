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
                ->options(collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => sprintf('%02d', $m)])->all())
                ->live(),
        ];
    }

    protected function getData(): array
    {
        $year  = (int) ($this->filterFormData['year'] ?? $this->getDefaultYear());
        $month = $this->filterFormData['month'] ?? null;
        $month = ($month === '' || $month === null) ? null : (int) $month;

        if ($month) {
            $reg = $this->countDangKy($year, $month);
            $done = $this->countHoanThanh($year, $month);
            [$totalNotDone, $vangP, $vangKP, $vangKhac] = $this->countKhongHoanThanhWithAbsence($year, $month);
            unset($totalNotDone);

            $datasets = [
                [
                    'label' => 'Đăng ký',
                    'data' => [$reg],
                    'borderRadius' => 8,
                    'stack' => 'dang-ky',
                ],
                [
                    'label' => 'Hoàn thành',
                    'data' => [$done],
                    'borderRadius' => 8,
                    'stack' => 'hoan-thanh',
                ],
                [
                    'label' => 'Không hoàn thành - Vắng P',
                    'data' => [$vangP],
                    'stack' => 'khong-hoan-thanh',
                    'borderRadius' => 8,
                ],
                [
                    'label' => 'Không hoàn thành - Vắng KP',
                    'data' => [$vangKP],
                    'stack' => 'khong-hoan-thanh',
                    'borderRadius' => 8,
                ],
            ];

            if ($vangKhac > 0) {
                $datasets[] = [
                    'label' => 'Không hoàn thành - Khác',
                    'data' => [$vangKhac],
                    'stack' => 'khong-hoan-thanh',
                    'borderRadius' => 8,
                ];
            }

            return [
                'datasets' => $datasets,
                'labels' => [sprintf('Tháng %02d/%d', $month, $year)],
            ];
        }

        $labels    = collect(range(1, 12))->map(fn ($m) => sprintf('%02d', $m))->all();
        $regs      = $this->monthlyCounter(fn ($y, $m) => $this->countDangKy($y, $m), $year);
        $dones     = $this->monthlyCounter(fn ($y, $m) => $this->countHoanThanh($y, $m), $year);
        $notDones  = $this->monthlyCounter(fn ($y, $m) => $this->countKhongHoanThanh($y, $m), $year);

        return [
            'datasets' => [
                [
                    'label' => 'Đăng ký',
                    'data' => $regs,
                    'borderRadius' => 8,
                ],
                [
                    'label' => 'Hoàn thành',
                    'data' => $dones,
                    'borderRadius' => 8,
                ],
                [
                    'label' => 'Không hoàn thành',
                    'data' => $notDones,
                    'borderRadius' => 8,
                ],
            ],
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
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => [
                    'stacked' => (bool) $detail, // stack khi xem chi tiết tháng để nhóm Vắng P/KP
                    'ticks'   => [ 'font' => [ 'size' => 12 ]],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => [ 'font' => [ 'size' => 12 ]],
                    'grid'        => [ 'drawBorder' => false ],
                ],
            ],
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

    private function monthlyCounter(callable $fn, int $year): array
    {
        $out = [];
        for ($m = 1; $m <= 12; $m++) { $out[] = (int) $fn($year, $m); }
        return $out;
    }

    private function countDangKy(int $year, int $month): int
    {
        $table = (new DangKy)->getTable();
        $dateColumn = $this->resolveDateColumn($table, ['created_at', 'ngay_dang_ky']);
        $query = DangKy::query();

        $this->applyPlanYearFilter($query, $table, $year);

        if ($dateColumn) {
            $query->whereYear("$table.$dateColumn", $year)
                ->whereMonth("$table.$dateColumn", $month);
        }

        return (int) $query->count();
    }

    private function countHoanThanh(int $year, int $month): int
    {
        $table = (new HocVienHoanThanh)->getTable();
        $dateColumn = $this->resolveDateColumn($table, ['ngay_hoan_thanh', 'created_at', 'updated_at']);
        $query = HocVienHoanThanh::query();

        $this->applyPlanYearFilter($query, $table, $year, 'khoa_hoc_id');

        if ($dateColumn) {
            $query->whereYear("$table.$dateColumn", $year)
                ->whereMonth("$table.$dateColumn", $month);
        }

        return (int) $query->count();
    }

    private function countKhongHoanThanh(int $year, int $month): int
    {
        return (int) $this->buildKhongHoanThanhQuery($year, $month)->count();
    }

    private function countKhongHoanThanhWithAbsence(int $year, int $month): array
    {
        $table = (new HocVienKhongHoanThanh)->getTable();
        $base  = $this->buildKhongHoanThanhQuery($year, $month);
        $total = (clone $base)->count();
        $vangP = 0;
        $vangKP = 0;

        if (Schema::hasColumn($table, 'vang_co_phep')) {
            $vangP  = (clone $base)->where("$table.vang_co_phep", 1)->count();
            $vangKP = (clone $base)->where("$table.vang_co_phep", 0)->count();
        } elseif (Schema::hasColumn($table, 'loai_vang')) {
            $vangP  = (clone $base)->whereIn("$table.loai_vang", ['p', 'phep', 'vang_p', 'Vắng P', 'Vang P'])->count();
            $vangKP = (clone $base)->whereIn("$table.loai_vang", ['kp', 'khong_phep', 'vang_kp', 'Vắng KP', 'Vang KP'])->count();
        } elseif (Schema::hasColumn($table, 'tinh_trang')) {
            $vangP  = (clone $base)->where(DB::raw('LOWER(' . $table . '.tinh_trang)'), 'like', '%p%')->count();
            $vangKP = max($total - $vangP, 0);
        }

        $vangKhac = max($total - $vangP - $vangKP, 0);

        return [$total, $vangP, $vangKP, $vangKhac];
    }

    private function buildKhongHoanThanhQuery(int $year, ?int $month = null): Builder
    {
        $table = (new HocVienKhongHoanThanh)->getTable();
        $dateColumn = $this->resolveDateColumn($table, ['ngay_khong_hoan_thanh', 'created_at', 'updated_at']);

        $query = HocVienKhongHoanThanh::query();

        $this->applyPlanYearFilter($query, $table, $year, 'khoa_hoc_id');

        if ($dateColumn) {
            $query->whereYear("$table.$dateColumn", $year);
            if ($month) {
                $query->whereMonth("$table.$dateColumn", $month);
            }
        }

        return $query;
    }

    private function applyPlanYearFilter(Builder $query, string $table, int $year, string $foreignKey = 'khoa_hoc_id'): void
    {
        $khoaHocTable = (new KhoaHoc)->getTable();

        if (!Schema::hasTable($khoaHocTable) || !Schema::hasColumn($khoaHocTable, 'nam')) {
            return;
        }

        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $foreignKey)) {
            return;
        }

        $query->join($khoaHocTable, "$khoaHocTable.id", '=', "$table.$foreignKey")
            ->where("$khoaHocTable.nam", $year);
    }

    private function resolveDateColumn(string $table, array $candidates, ?string $fallback = 'created_at'): ?string
    {
        foreach ($candidates as $column) {
            if ($column && Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        if ($fallback && Schema::hasColumn($table, $fallback)) {
            return $fallback;
        }

        return null;
    }
}
