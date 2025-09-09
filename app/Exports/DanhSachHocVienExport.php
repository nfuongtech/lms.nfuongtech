<?php

namespace App\Exports;

use App\Models\DangKy;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DanhSachHocVienExport implements FromCollection, WithHeadings, WithMapping
{
    protected $khoaHocId;

    public function __construct($khoaHocId)
    {
        $this->khoaHocId = $khoaHocId;
    }

    public function collection()
    {
        return DangKy::with('hocVien.donVi')->where('khoa_hoc_id', $this->khoaHocId)->get();
    }

    public function map($dangKy): array
    {
        return [
            $dangKy->hocVien->msnv ?? 'N/A',
            $dangKy->hocVien->ho_ten ?? 'N/A',
            $dangKy->hocVien->nam_sinh ? date('d/m/Y', strtotime($dangKy->hocVien->nam_sinh)) : 'N/A',
            $dangKy->hocVien->chuc_vu ?? 'N/A',
            $dangKy->hocVien->donVi->ten_hien_thi ?? 'N/A',
            $dangKy->hocVien->email ?? 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'MSNV',
            'Họ và Tên',
            'Năm sinh',
            'Chức vụ',
            'Đơn vị',
            'Email',
        ];
    }
}
