<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; // Thêm dòng này

class DangKy extends Model
{
    use HasFactory;

    protected $fillable = [
        'hoc_vien_id',
        'khoa_hoc_id',
    ];

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class);
    }

    public function hocVien(): BelongsTo
    {
        return $this->belongsTo(HocVien::class);
    }

    /**
     * Mối quan hệ: Một Lượt ghi danh có nhiều Lượt điểm danh.
     */
    public function diemDanhs(): HasMany
    {
        return $this->hasMany(DiemDanh::class);
    }

    /**
     * Mối quan hệ: Một Lượt ghi danh có một Kết quả cuối khóa.
     */
    public function ketQuaKhoaHoc(): HasOne
    {
        return $this->hasOne(KetQuaKhoaHoc::class);
    }
}
