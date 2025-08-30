<?php

namespace App\Filament\Widgets;

use App\Models\KetQuaKhoaHoc;
use Filament\Widgets\ChartWidget;

class TrainingCostChart extends ChartWidget
{
    protected static ?string $heading = 'Chi phí đào tạo theo Đơn vị';

    protected function getData(): array
    {
        // Truy vấn dữ liệu để tính toán
        $data = KetQuaKhoaHoc::query()
            ->join('dang_kies', 'ket_qua_khoa_hocs.dang_ky_id', '=', 'dang_kies.id')
            ->join('hoc_viens', 'dang_kies.hoc_vien_id', '=', 'hoc_viens.id')
            ->join('don_vis', 'hoc_viens.don_vi_id', '=', 'don_vis.id')
            ->selectRaw('don_vis.tap_doan_don_vi as don_vi, SUM(ket_qua_khoa_hocs.hoc_phi) as total_cost')
            ->groupBy('don_vis.tap_doan_don_vi')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Tổng chi phí (VNĐ)',
                    'data' => $data->pluck('total_cost')->toArray(),
                ],
            ],
            'labels' => $data->pluck('don_vi')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Loại biểu đồ: cột
    }
}
