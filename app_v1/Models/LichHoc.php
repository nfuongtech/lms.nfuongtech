<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichHoc extends Model
{
    use HasFactory;

    protected $fillable = [
        'khoa_hoc_id',
        'chuyen_de_id',
        'giang_vien_id',
        'ngay_hoc',
        'gio_bat_dau',
        'gio_ket_thuc',
        'dia_diem',
    ];

    // Quan hệ ngược với KhoaHoc
    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    // Quan hệ với Giảng viên
    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    // Quan hệ với Chuyên đề
    public function chuyenDe()
    {
        return $this->belongsTo(ChuyenDe::class, 'chuyen_de_id');
    }
}
