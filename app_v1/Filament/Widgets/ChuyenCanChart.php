<?php

namespace App\Filament\Widgets;

use App\Models\DiemDanh;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChuyenCanChart extends ChartWidget
{
    protected static ?string $heading = 'Thống kê Chuyên cần';

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Tuần này',
            'month' => 'Tháng này',
        ];
    }

    protected function getData(): array
    {
        $query = DiemDanh::query();

        if ($this->filter === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } else {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        }

        $data = $query
            ->select('trang_thai', DB::raw('count(*) as count'))
            ->groupBy('trang_thai')
            ->pluck('count', 'trang_thai');

        return [
            'datasets' => [
                [
                    'label' => 'Chuyên cần',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => [
                        'rgb(54, 162, 235)', // Xanh dương cho 'Có mặt'
                        'rgb(255, 205, 86)', // Vàng cho 'Phép'
                        'rgb(255, 99, 132)',  // Đỏ cho 'Không phép'
                    ],
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Đã đổi thành biểu đồ cột
    }
}
