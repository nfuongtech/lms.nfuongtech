<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HocVienHoanThanh extends Model
{
    use HasFactory;

    protected $table = 'hoc_vien_hoan_thanh';

    protected $fillable = [
        'hoc_vien_id',
        'khoa_hoc_id',
        'ket_qua_khoa_hoc_id',
        'ngay_hoan_thanh',
        'chung_chi_da_cap',
        'ghi_chu',
    ];

    // ====== QUAN HỆ BỔ SUNG ======
    public function hocVien()
    {
        return $this->belongsTo(HocVien::class, 'hoc_vien_id');
    }

    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function ketQuaKhoaHoc()
    {
        return $this->belongsTo(KetQuaKhoaHoc::class, 'ket_qua_khoa_hoc_id');
    }
}
