<?php

namespace App\Filament\Widgets;

use App\Models\DangKy;
use App\Models\HocVienHoanThanh;
use App\Models\KhoaHoc;
use App\Models\QuyTacMaKhoa;
use Filament\Forms;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChiPhiDaoTaoChart extends ChartWidget
{
    protected static ?string $heading = 'Chi phí đào tạo theo tháng';
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

            Forms\Components\MultiSelect::make('loai_hinh')
                ->label('Loại hình đào tạo')
                ->placeholder('Tất cả loại hình')
                ->options($this->getLoaiHinhOptions()) // nhãn sạch, key là giá trị gốc
                ->live(),
        ];
    }

    protected function getData(): array
    {
        $year = (int) ($this->filterFormData['year'] ?? $this->getDefaultYear());
        $selectedLoaiHinh = (array) ($this->filterFormData['loai_hinh'] ?? []);

        $labels = collect(range(1, 12))->map(fn ($m) => sprintf('%02d', $m))->all();
        $values = [];

        for ($m = 1; $m <= 12; $m++) {
            $values[] = $this->sumCost($year, $m, $selectedLoaiHinh);
        }

        return [
            'datasets' => [[
                'label' => 'Tổng chi phí (VND)',
                'data' => $values,
                'borderRadius' => 8,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [ 'duration' => 900, 'easing' => 'easeOutCubic' ],
            'plugins'   => [
                'legend'  => [ 'position' => 'top', 'labels' => [ 'usePointStyle' => true ]],
                'tooltip' => [
                    'callbacks' => [
                        'label' => new \Illuminate\Support\Js(<<<'JS'
                            (ctx) => {
                                const v = ctx.parsed.y || 0;
                                return `${ctx.dataset.label}: ${v.toLocaleString('vi-VN')}₫`;
                            }
                        JS),
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => new \Illuminate\Support\Js(<<<'JS'
                            (value) => {
                                const n = Number(value);
                                if (Math.abs(n) >= 1_000_000_000) return (n/1_000_000_000).toFixed(1).replace(/\.0$/,'') + ' tỷ';
                                if (Math.abs(n) >= 1_000_000)     return (n/1_000_000).toFixed(1).replace(/\.0$/,'') + ' triệu';
                                return n.toLocaleString('vi-VN');
                            }
                        JS),
                    ],
                    'grid' => [ 'drawBorder' => false ],
                ],
                'x' => [ 'ticks' => [ 'font' => [ 'size' => 12 ]]],
            ],
        ];
    }

    /**
     * Trả về [RAW_VALUE => CLEAN_LABEL], bỏ "V/v" ở đầu nhãn.
     */
    private function getLoaiHinhOptions(): array
    {
        $labels = collect();

        if (Schema::hasTable((new KhoaHoc)->getTable()) && Schema::hasColumn((new KhoaHoc)->getTable(), 'loai_hinh_dao_tao')) {
            $labels = $labels->merge(
                KhoaHoc::query()
                    ->whereNotNull('loai_hinh_dao_tao')
                    ->distinct()
                    ->pluck('loai_hinh_dao_tao')
            );
        }

        if (class_exists(QuyTacMaKhoa::class)
            && Schema::hasTable((new QuyTacMaKhoa)->getTable())
            && Schema::hasColumn((new QuyTacMaKhoa)->getTable(), 'loai_hinh')) {
            $labels = $labels->merge(
                QuyTacMaKhoa::query()
                    ->whereNotNull('loai_hinh')
                    ->distinct()
                    ->pluck('loai_hinh')
            );
        }

        $labels = $labels->filter()->unique()->values();

        return $labels->mapWithKeys(function ($raw) {
            $clean = preg_replace('/^\s*[Vv]\s*/u', '', (string) $raw);
            return [$raw => $clean === '' ? (string) $raw : $clean];
        })->all();
    }

    private function sumCost(int $year, int $month, array $selectedLoaiHinh): float
    {
        $hvht    = (new HocVienHoanThanh)->getTable();
        $dangKy  = (new DangKy)->getTable();
        $khoaHoc = (new KhoaHoc)->getTable();

        $query = HocVienHoanThanh::query();
        $dateColumn = $this->resolveDateColumn($hvht, ['ngay_hoan_thanh', 'created_at', 'updated_at']);

        if ($dateColumn) {
            $query->whereYear("$hvht.$dateColumn", $year)
                ->whereMonth("$hvht.$dateColumn", $month);
        }

        $joinedKhoaHoc = false;

        if (Schema::hasTable($khoaHoc)) {
            if (Schema::hasColumn($hvht, 'khoa_hoc_id')) {
                $query->leftJoin($khoaHoc, "$khoaHoc.id", '=', "$hvht.khoa_hoc_id");
                $joinedKhoaHoc = true;
            } elseif (
                Schema::hasTable($dangKy)
                && Schema::hasColumn($dangKy, 'khoa_hoc_id')
                && Schema::hasColumn($hvht, 'dang_ky_id')
            ) {
                $query->leftJoin($dangKy, "$dangKy.id", '=', "$hvht.dang_ky_id");
                $query->leftJoin($khoaHoc, "$khoaHoc.id", '=', "$dangKy.khoa_hoc_id");
                $joinedKhoaHoc = true;
            }
        } elseif (Schema::hasTable($dangKy) && Schema::hasColumn($hvht, 'dang_ky_id')) {
            $query->leftJoin($dangKy, "$dangKy.id", '=', "$hvht.dang_ky_id");
        }

        if ($joinedKhoaHoc && Schema::hasColumn($khoaHoc, 'nam')) {
            $query->where("$khoaHoc.nam", $year);
        }

        if (!empty($selectedLoaiHinh) && $joinedKhoaHoc && Schema::hasColumn($khoaHoc, 'loai_hinh_dao_tao')) {
            $query->whereIn("$khoaHoc.loai_hinh_dao_tao", $selectedLoaiHinh);
        }

        $costColumns = collect(['tong_chi_phi', 'chi_phi', 'chi_phi_dao_tao'])
            ->filter(fn ($column) => Schema::hasColumn($hvht, $column))
            ->map(fn ($column) => "$hvht.$column")
            ->values();

        if ($costColumns->isEmpty()) {
            return 0.0;
        }

        $coalesceExpression = 'COALESCE(' . $costColumns->implode(', ') . ', 0)';

        return (float) $query->sum(DB::raw($coalesceExpression));
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
        $table = (new KhoaHoc)->getTable();

        if (Schema::hasTable($table) && Schema::hasColumn($table, 'nam')) {
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
