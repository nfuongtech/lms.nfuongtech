<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MauNhapGiangVienExport implements FromArray, WithHeadings
{
    /**
    * @return array
    */
    public function array(): array
    {
        return [
            [
                'GV001', 'Nguyễn Văn A', 'Nam', '01/01/1990', 'nguyenvana@example.com', '0987654321',
                '01/01/2020', 'Nhân viên', 'DV001', 'Phòng Kế toán', 'THACO',
                'Ban QTNS', 'Phòng KT', 'Tòa nhà A', 'Đang giảng dạy',
                'Thạc sĩ', 'CNTT', '5', 'Có kinh nghiệm giảng dạy...', '123 Đường XYZ, Quận 1, TP.HCM',
                // --- THÊM: Dữ liệu mẫu Đơn vị pháp nhân ---
                'MST001', 'Công ty ABC', '123 Đường XYZ, Quận 1, TP.HCM', 'Ghi chú mẫu'
                // --- HẾT THÊM: Dữ liệu mẫu Đơn vị pháp nhân ---
            ]
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Mã số', 'Họ và tên', 'Giới tính', 'Năm sinh (dd/mm/yyyy)', 'Email', 'Số điện thoại',
            'Ngày vào (dd/mm/yyyy)', 'Chức vụ', 'Mã đơn vị', 'Tên đơn vị', 'THACO/TĐTV',
            'Công ty/Ban NVQT', 'Phòng/Bộ phận', 'Nơi làm việc chi tiết', 'Tình trạng',
            'Trình độ', 'Chuyên môn', 'Số năm kinh nghiệm', 'Tóm tắt kinh nghiệm', 'Hộ khẩu/Nơi làm việc',
            // --- THÊM: Tiêu đề cột Đơn vị pháp nhân ---
            'Mã đơn vị pháp nhân', 'Tên đơn vị pháp nhân', 'Địa chỉ', 'Ghi chú'
            // --- HẾT THÊM: Tiêu đề cột Đơn vị pháp nhân ---
        ];
    }
}
