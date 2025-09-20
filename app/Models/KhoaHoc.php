<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ChuongTrinh;
use App\Models\DangKy;
use App\Models\HocVien;
use App\Models\LichHoc;

class KhoaHoc extends Model
{
    // use HasFactory;

    protected $fillable = [
        'chuong_trinh_id',
        'ma_khoa_hoc',
        'nam',
        'trang_thai', // Chỉ dùng: Dự thảo, Ban hành, Đang đào tạo, Kết thúc
        'ghi_chu',
    ];

    protected $casts = [
        // 'nam' => 'integer',
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime',
    ];

    // --- Quan hệ ---
    public function chuongTrinh()
    {
        return $this->belongsTo(ChuongTrinh::class, 'chuong_trinh_id');
    }

    public function dangKys()
    {
        return $this->hasMany(DangKy::class, 'khoa_hoc_id');
    }

    public function lichHocs()
    {
        return $this->hasMany(LichHoc::class, 'khoa_hoc_id');
    }

    public function hocViens()
    {
        return $this->belongsToMany(HocVien::class, 'dang_kies', 'khoa_hoc_id', 'hoc_vien_id');
    }

    public function getSoLuongHocVienAttribute()
    {
        return $this->dangKys()->count();
    }

    // --- Chuẩn hóa trạng thái ---
    public function getTrangThaiAttribute($value)
    {
        // Nếu DB còn lưu "Kế hoạch" thì ép thành "Ban hành"
        if ($value === 'Kế hoạch') {
            return 'Ban hành';
        }
        return $value;
    }

    public function setTrangThaiAttribute($value)
    {
        // Nếu ai đó gán "Kế hoạch" → tự động đổi thành "Ban hành"
        if ($value === 'Kế hoạch') {
            $this->attributes['trang_thai'] = 'Ban hành';
        } else {
            $this->attributes['trang_thai'] = $value;
        }
    }
}
