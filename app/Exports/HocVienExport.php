<?php

namespace App\Exports;

use App\Models\HocVien;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HocVienExport implements FromQuery, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        return HocVien::with('donVi');
    }

    /**
     * Map each row to an array for Excel.
     *
     * @param HocVien $hocVien
     * @return array
     */
    public function map($hocVien): array
    {
        return [
            $hocVien->msnv,
            $hocVien->ho_ten,
            $hocVien->gioi_tinh,
            $hocVien->nam_sinh ? date('d/m/Y', strtotime($hocVien->nam_sinh)) : 'N/A',
            $hocVien->email,
            $hocVien->ngay_vao ? date('d/m/Y', strtotime($hocVien->ngay_vao)) : 'N/A',
            $hocVien->chuc_vu,
            $hocVien->donVi->ma_don_vi ?? 'N/A',
            $hocVien->donVi->ten_hien_thi ?? 'N/A',
            $hocVien->donVi->thaco_tdtv ?? 'N/A',
            $hocVien->donVi->cong_ty_ban_nvqt ?? 'N/A',
            $hocVien->donVi->phong_bo_phan ?? 'N/A',
            $hocVien->donVi->noi_lam_viec_chi_tiet ?? 'N/A',
            $hocVien->tinh_trang,
            $hocVien->hinh_anh_path,
        ];
    }

    /**
     * Define the headings for the Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'MSNV',
            'Họ và tên',
            'Giới tính',
            'Năm sinh',
            'Email',
            'Ngày vào',
            'Chức vụ',
            'Mã đơn vị',
            'Tên đơn vị',
            'THACO/TĐTV',
            'Công ty/Ban NVQT',
            'Phòng/Bộ phận',
            'Nơi làm việc chi tiết',
            'Tình trạng',
            'Đường dẫn hình ảnh',
        ];
    }
}
