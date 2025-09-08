<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichHoc extends Model
{
    use HasFactory;

    protected $table = 'lich_hocs';

    protected $fillable = [
        'khoa_hoc_id',
        'chuyen_de_id',
        'giang_vien_id',
        'ngay_hoc',
        'gio_bat_dau',
        'gio_ket_thuc',
        'dia_diem',
        'tuan',
        'thang',
        'nam',
        'buoi', // số buổi/phiên (đã thêm)
        'ghi_chu',
    ];

    protected $casts = [
        'ngay_hoc' => 'date',
        'gio_bat_dau' => 'time',
        'gio_ket_thuc' => 'time',
    ];

    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function chuyenDe()
    {
        return $this->belongsTo(ChuyenDe::class, 'chuyen_de_id');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }
}
