<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MauNhapDiemDanhHocVienExport implements FromArray, WithHeadings
{
    /**
    * @return array
    */
    public function array(): array
    {
        return [
            [
                'HV001', 'Nguyễn Văn A', 'Nam', '01/01/1990', 'nguyenvana@example.com',
                '01/01/2020', 'Nhân viên', 'DV001', 'Phòng Kế toán', 'THACO',
                'Ban QTNS', 'Phòng KT', 'Tòa nhà A', 'Đang làm việc',
                // --- THÊM: Dữ liệu mẫu Đơn vị pháp nhân ---
                'MST001', 'Công ty ABC', '123 Đường XYZ, Quận 1, TP.HCM', 'Ghi chú mẫu'
                // --- HẾT THÊM: Dữ liệu mẫu Đơn vị pháp nhân ---
                '01/01/2025', '15:30', '17:30', 'Phòng họp A', 'co_mat', '', '8.5'
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
            'Giới tính',
            'Năm sinh (dd/mm/yyyy)',
            'Email',
            'Ngày vào (dd/mm/yyyy)',
            'Chức vụ',
            'Mã đơn vị',
            'Tên đơn vị',
            'THACO/TĐTV',
            'Công ty/Ban NVQT',
            'Phòng/Bộ phận',
            'Nơi làm việc chi tiết',
            'Tình trạng',
            // --- THÊM: Tiêu đề cột Đơn vị pháp nhân ---
            'Mã đơn vị pháp nhân',
            'Tên đơn vị pháp nhân',
            'Địa chỉ',
            'Ghi chú'
            // --- HẾT THÊM: Tiêu đề cột Đơn vị pháp nhân ---
            'Ngày học (dd/mm/yyyy)',
            'Giờ bắt đầu (24h, HH:MM)',
            'Giờ kết thúc (24h, HH:MM)',
            'Địa điểm',
            'Trạng thái điểm danh (co_mat/vang_phep/vang_khong_phep)',
            'Lý do vắng',
            'Điểm buổi học (0-10)'
        ];
    }
}
