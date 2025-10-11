<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HocVienHoanThanh extends Model
{
    // table mặc định: hoc_vien_hoan_thanhs
    protected $fillable = [
        'hoc_vien_id',
        'khoa_hoc_id',
        'ket_qua_khoa_hoc_id',
        'ngay_hoan_thanh',
        'chi_phi_dao_tao',
        'chung_chi_link',
        'chung_chi_file_path',
        'chung_chi_da_cap',
        'ghi_chu',
    ];

    public function hocVien()
    {
        return $this->belongsTo(HocVien::class, 'hoc_vien_id');
    }

    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function ketQua()
    {
        return $this->belongsTo(KetQuaKhoaHoc::class, 'ket_qua_khoa_hoc_id');
    }
}
