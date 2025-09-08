<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChuongTrinh extends Model
{
    use HasFactory;

    protected $table = 'chuong_trinhs';

    protected $fillable = [
        'ma_chuong_trinh',
        'ten_chuong_trinh',
        'thoi_luong',
        'muc_tieu_dao_tao',
        'loai_hinh_dao_tao',
        'tinh_trang',
    ];

    /**
     * Các chuyên đề thuộc chương trình (pivot chuong_trinh_chuyen_de).
     * Lược đồ DB có bảng chuong_trinh_chuyen_de. :contentReference[oaicite:3]{index=3}
     */
    public function chuyenDes(): BelongsToMany
    {
        return $this->belongsToMany(ChuyenDe::class, 'chuong_trinh_chuyen_de', 'chuong_trinh_id', 'chuyen_de_id');
    }

    /**
     * Scope lấy chương trình đang áp dụng
     */
    public function scopeActive($query)
    {
        return $query->where('tinh_trang', 'Đang áp dụng');
    }
}
