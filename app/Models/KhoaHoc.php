<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KhoaHoc extends Model
{
    use HasFactory;

    protected $fillable = [
        'chuyen_de_id',
        'ten_khoa_hoc',
        'nam',
        'trang_thai',
    ];

    /**
     * Mối quan hệ: Một Khóa học thuộc về một Chuyên đề.
     */
    public function chuyenDe(): BelongsTo
    {
        return $this->belongsTo(ChuyenDe::class);
    }

    /**
     * Mối quan hệ: Một Khóa học có nhiều Buổi học.
     */
    public function lichHocs(): HasMany
    {
        return $this->hasMany(LichHoc::class);
    }

    /**
     * Mối quan hệ: Một Khóa học có nhiều Lượt ghi danh.
     */
    public function dangKys(): HasMany
    {
        return $this->hasMany(DangKy::class);
    }
}
