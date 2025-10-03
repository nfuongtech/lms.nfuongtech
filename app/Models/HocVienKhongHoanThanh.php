<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HocVienKhongHoanThanh extends Model
{
    // table mặc định: hoc_vien_khong_hoan_thanhs
    protected $fillable = [
        'hoc_vien_id',
        'khoa_hoc_id',
        'ket_qua_khoa_hoc_id',
        'ly_do_khong_hoan_thanh',
        'co_the_ghi_danh_lai',
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
