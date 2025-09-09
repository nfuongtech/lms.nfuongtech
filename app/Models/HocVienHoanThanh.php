<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HocVienHoanThanh extends Model
{
    protected $fillable = [
        'hoc_vien_id',
        'khoa_hoc_id',
        'ket_qua_khoa_hoc_id',
        'ngay_hoan_thanh',
        'chung_chi_da_cap',
        'ghi_chu',
    ];

    protected $casts = [
        'ngay_hoan_thanh' => 'date',
        'chung_chi_da_cap' => 'boolean',
    ];

    public function hocVien()
    {
        return $this->belongsTo(HocVien::class);
    }

    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class);
    }

    public function ketQuaKhoaHoc()
    {
        return $this->belongsTo(KetQuaKhoaHoc::class);
    }
}
