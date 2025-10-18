<?php

namespace App\Filament\Widgets;

use App\Models\DangKy;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use Filament\Forms;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ThongKeHocVienChart extends ChartWidget
{
    protected static ?string $heading = 'Thống kê Học viên theo tháng';
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
            [$notDone, $vangP, $vangKP] = $this->countKhongHoanThanhWithAbsence($year, $month);

            return [
                'datasets' => [
                    [
                        'label' => 'Đăng ký',
                        'data' => [$reg],
                        'borderRadius' => 8,
                    ],
                    [
                        'label' => 'Hoàn thành',
                        'data' => [$done],
                        'borderRadius' => 8,
                    ],
                    [
                        'label' => 'Không hoàn thành (Tổng)',
                        'data' => [$notDone],
                        'borderRadius' => 8,
                    ],
                    [
                        'label' => '— Vắng P',
                        'data' => [$vangP],
                        'stack' => 'khong-hoan-thanh',
                        'borderRadius' => 8,
                    ],
                    [
                        'label' => '— Vắng KP',
                        'data' => [$vangKP],
                        'stack' => 'khong-hoan-thanh',
                        'borderRadius' => 8,
                    ],
                ],
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
        return [
            'animation' => [ 'duration' => 900, 'easing' => 'easeOutQuart' ],
            'plugins'   => [
                'legend'  => [ 'position' => 'top', 'labels' => [ 'usePointStyle' => true ]],
                'tooltip' => [
                    'mode' => 'index', 'intersect' => false,
                    'callbacks' => [
                        'label' => new \Illuminate\Support\Js(<<<'JS'
                            (ctx) => `${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('vi-VN')}`
                        JS),
                    ],
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
        return (int) now()->year; // Nếu có “Năm kế hoạch”, thay logic tại đây
    }

    private function getAvailableYears(): array
    {
        $years = DangKy::query()
            ->selectRaw('DISTINCT YEAR(created_at) as y')
            ->orderBy('y', 'desc')
            ->pluck('y')
            ->toArray();

        if (empty($years)) { $years = [now()->year]; }

        return collect($years)->mapWithKeys(fn ($y) => [$y => (string) $y])->all();
    }

    private function monthlyCounter(callable $fn, int $year): array
    {
        $out = [];
        for ($m = 1; $m <= 12; $m++) { $out[] = (int) $fn($year, $m); }
        return $out;
    }

    private function countDangKy(int $year, int $month): int
    {
        return DangKy::query()->whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
    }

    private function countHoanThanh(int $year, int $month): int
    {
        return HocVienHoanThanh::query()->whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
    }

    private function countKhongHoanThanh(int $year, int $month): int
    {
        return HocVienKhongHoanThanh::query()->whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
    }

    private function countKhongHoanThanhWithAbsence(int $year, int $month): array
    {
        $table = (new HocVienKhongHoanThanh)->getTable();
        $base  = HocVienKhongHoanThanh::query()->whereYear('created_at', $year)->whereMonth('created_at', $month);
        $total = (clone $base)->count();
        $vangP = 0; $vangKP = 0;

        if (Schema::hasColumn($table, 'vang_co_phep')) {
            $vangP  = (clone $base)->where('vang_co_phep', 1)->count();
            $vangKP = (clone $base)->where('vang_co_phep', 0)->count();
        } elseif (Schema::hasColumn($table, 'loai_vang')) {
            $vangP  = (clone $base)->whereIn('loai_vang', ['p','phep','vang_p','Vắng P','Vang P'])->count();
            $vangKP = (clone $base)->whereIn('loai_vang', ['kp','khong_phep','vang_kp','Vắng KP','Vang KP'])->count();
        } elseif (Schema::hasColumn($table, 'tinh_trang')) {
            $vangP  = (clone $base)->where(DB::raw('LOWER(tinh_trang)'), 'like', '%p%')->count();
            $vangKP = max($total - $vangP, 0);
        }

        return [$total, $vangP, $vangKP];
    }
}
