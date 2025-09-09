<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KetQuaKhoaHoc extends Model
{
    protected $fillable = [
        'dang_ky_id',
        'diem_tong_khoa',
        'ket_qua',
        'chi_phi',
        'trang_thai_hoc_vien',
    ];

    protected $casts = [
        'ket_qua' => 'string',
        'trang_thai_hoc_vien' => 'string',
    ];

    public function dangKy()
    {
        return $this->belongsTo(DangKy::class);
    }

    public function hocVienHoanThanh()
    {
        return $this->hasOne(HocVienHoanThanh::class, 'ket_qua_khoa_hoc_id');
    }

    public function hocVienKhongHoanThanh()
    {
        return $this->hasOne(HocVienKhongHoanThanh::class, 'ket_qua_khoa_hoc_id');
    }
}
