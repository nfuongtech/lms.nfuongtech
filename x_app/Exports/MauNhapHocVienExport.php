<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class MauNhapHocVienExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Ví dụ 1 dòng mẫu để người dùng dễ hình dung
        return new Collection([
            [
                'HV-250901',
                'Nguyễn Văn A',
                'Nam',
                '01/01/2000',
                '01/09/2025',
                'Kỹ sư',
                'vana@example.com',
                '0912345678',
                'Đang làm việc',
                'THACO',
                'Ban Công nghệ',
                'Phòng IT',
                'Quảng Nam',
                'DV001',
                '0400123456',
                'Công ty THACO',
                'KCN Chu Lai, Quảng Nam',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'msnv',
            'ho_ten',
            'gioi_tinh',
            'nam_sinh',
            'ngay_vao',
            'chuc_vu',
            'email',
            'sdt',
            'tinh_trang',
            'thaco_tdtv',
            'cong_ty_ban_nvqt',
            'phong_bo_phan',
            'noi_lam_viec_chi_tiet',
            'ma_don_vi',
            'ma_so_thue',
            'ten_don_vi',
            'dia_chi',
        ];
    }
}
