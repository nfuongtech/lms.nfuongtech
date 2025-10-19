<?php

namespace App\Exports;

use App\Models\HocVien;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HocVienExport implements FromCollection, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query ?? HocVien::query();
    }

    public function collection()
    {
        return $this->query->with(['donVi', 'donViPhapNhan'])->get();
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

    public function map($hocVien): array
    {
        return [
            $hocVien->msnv,
            $hocVien->ho_ten,
            $hocVien->gioi_tinh,
            optional($hocVien->nam_sinh)->format('d/m/Y'),
            optional($hocVien->ngay_vao)->format('d/m/Y'),
            $hocVien->chuc_vu,
            $hocVien->email,
            $hocVien->sdt,
            $hocVien->tinh_trang,
            $hocVien->donVi?->thaco_tdtv,
            $hocVien->donVi?->cong_ty_ban_nvqt,
            $hocVien->donVi?->phong_bo_phan,
            $hocVien->donVi?->noi_lam_viec_chi_tiet,
            $hocVien->donVi?->ma_don_vi,
            $hocVien->donViPhapNhan?->ma_so_thue,
            $hocVien->donViPhapNhan?->ten_don_vi,
            $hocVien->donViPhapNhan?->dia_chi,
        ];
    }
}
