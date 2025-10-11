<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KetQuaKhoaHoc extends Model
{
    use HasFactory;

    protected $table = 'ket_qua_khoa_hocs';

    protected $fillable = [
        'dang_ky_id',
        'diem_tong_khoa',
        'diem_trung_binh',
        'tong_gio_hoc',
        'ket_qua',        // 'hoan_thanh' | 'khong_hoan_thanh' (mapping Đạt/Không đạt)
        'can_hoc_lai',    // 0|1
        'da_chuyen_duyet',
        'danh_gia_ren_luyen',
    ];

    public function dangKy()
    {
        return $this->belongsTo(DangKy::class, 'dang_ky_id');
    }
}
