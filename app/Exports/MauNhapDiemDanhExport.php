<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MauNhapDiemDanhExport implements FromArray, WithHeadings
{
    /**
    * @return array
    */
    public function array(): array
    {
        return [
            [
                'HV001', 'Nguyễn Văn A', '01/01/2025', '08:00', '17:00', 'co_mat', '', '8.5',
                'KH-ABC123', 'Chương trình đào tạo ABC'
            ]
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'MSNV',
            'Họ và tên',
            'Ngày học (dd/mm/yyyy)',
            'Giờ bắt đầu (HH:MM)',
            'Giờ kết thúc (HH:MM)',
            'Trạng thái (co_mat/vang_phep/vang_khong_phep)',
            'Lý do vắng',
            'Điểm buổi học (0-10)',
            'Mã khóa học',
            'Tên chương trình',
        ];
    }
}
