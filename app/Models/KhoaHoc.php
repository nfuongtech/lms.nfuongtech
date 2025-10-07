<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KhoaHoc extends Model
{
    use HasFactory;

    protected $table = 'khoa_hocs';

    protected $fillable = [
        'ma_khoa_hoc',
        'ten_khoa_hoc',
        'chuong_trinh_id',
        'nam',
        'yeu_cau_phan_tram_gio',
        'yeu_cau_diem_tb',
    ];

    protected $casts = [
        // Bảo đảm hiển thị đúng định dạng
        'yeu_cau_phan_tram_gio' => 'integer',
        'yeu_cau_diem_tb'       => 'decimal:1',
    ];

    public function chuongTrinh()
    {
        return $this->belongsTo(ChuongTrinh::class, 'chuong_trinh_id');
    }

    public function lichHocs()
    {
        return $this->hasMany(LichHoc::class, 'khoa_hoc_id');
    }

    // Dự phòng: nếu 'ten_khoa_hoc' trống thì lấy theo chương trình
    public function getTenKhoaHocAttribute(): string
    {
        $val = $this->attributes['ten_khoa_hoc'] ?? null;
        if ($val !== null && $val !== '') return (string) $val;
        return (string) ($this->chuongTrinh->ten_chuong_trinh ?? '');
    }
}
