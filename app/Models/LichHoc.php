<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LichHoc extends Model
{
    use HasFactory;

    protected $fillable = [
        'khoa_hoc_id',
        'giang_vien_id',
        'ngay_hoc',
        'gio_bat_dau',
        'gio_ket_thuc',
        'dia_diem',
    ];

    /**
     * Mối quan hệ: Một Buổi học thuộc về một Khóa học.
     */
    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class);
    }

    /**
     * Mối quan hệ: Một Buổi học được dạy bởi một Giảng viên.
     */
    public function giangVien(): BelongsTo
    {
        return $this->belongsTo(GiangVien::class);
    }
}
