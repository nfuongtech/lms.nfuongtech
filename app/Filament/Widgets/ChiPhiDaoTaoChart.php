<?php

namespace App\Filament\Widgets;

use App\Models\DangKy;
use App\Models\HocVienHoanThanh;
use App\Models\KhoaHoc;
use App\Models\QuyTacMaKhoa;
use Filament\Forms;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChiPhiDaoTaoChart extends ChartWidget
{
    protected static ?string $heading = 'Chi phí đào tạo theo tháng';
    protected static ?string $maxHeight = '380px';
    protected int|string|array $columnSpan = ['md' => 12, 'xl' => 6];

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('year')
                ->label('Năm')
                ->options($this->getAvailableYears())
                ->default(now()->year)
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
        $year = (int) ($this->filterFormData['year'] ?? now()->year);
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

    private function getAvailableYears(): array
    {
        $years = HocVienHoanThanh::query()
            ->selectRaw('DISTINCT YEAR(created_at) as y')
            ->orderBy('y','desc')
            ->pluck('y')
            ->toArray();

        if (empty($years)) { $years = [now()->year]; }

        return collect($years)->mapWithKeys(fn ($y) => [$y => (string) $y])->all();
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
        $hvht = (new HocVienHoanThanh)->getTable();
        $dk   = (new DangKy)->getTable();
        $kh   = (new KhoaHoc)->getTable();

        $q = HocVienHoanThanh::query()
            ->whereYear("$hvht.created_at", $year)
            ->whereMonth("$hvht.created_at", $month);

        if (Schema::hasTable($dk) && Schema::hasTable($kh) && Schema::hasColumn($kh, 'loai_hinh_dao_tao')) {
            $q->leftJoin($dk, "$dk.id", '=', "$hvht.dang_ky_id")
              ->leftJoin($kh, "$kh.id", '=', "$dk.khoa_hoc_id");

            if (!empty($selectedLoaiHinh)) {
                $q->whereIn("$kh.loai_hinh_dao_tao", $selectedLoaiHinh); // dùng GIÁ TRỊ RAW
            }
        }

        $sum = (clone $q)->sum(DB::raw("COALESCE($hvht.tong_chi_phi, $hvht.chi_phi, 0)"));
        return (float) $sum;
    }
}
