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
        'ket_qua',        // 'hoan_thanh' | 'khong_hoan_thanh' (mapping Đạt/Không đạt)
        'can_hoc_lai',    // 0|1
    ];

    public function dangKy()
    {
        return $this->belongsTo(DangKy::class, 'dang_ky_id');
    }
}
