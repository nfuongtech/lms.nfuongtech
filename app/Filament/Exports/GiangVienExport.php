<?php

namespace App\Filament\Exports;

use App\Models\GiangVien;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GiangVienExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return GiangVien::select(
            'ma_so',
            'ho_ten',
            'gioi_tinh',
            'nam_sinh',
            'email',
            'dien_thoai',
            'don_vi',
            'trinh_do',
            'chuyen_mon',
            'tinh_trang'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Mã số',
            'Họ tên',
            'Giới tính',
            'Năm sinh',
            'Email',
            'Điện thoại',
            'Đơn vị',
            'Trình độ',
            'Chuyên môn',
            'Tình trạng',
        ];
    }

    public function download($filename)
    {
        return Excel::download($this, $filename);
    }
}
