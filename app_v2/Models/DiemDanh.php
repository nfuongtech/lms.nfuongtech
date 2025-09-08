<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiemDanh extends Model
{
    use HasFactory;

    protected $fillable = [
        'dang_ky_id',
        'lich_hoc_id',
        'trang_thai',
        'ly_do_vang',
        'diem_buoi_hoc', // Thêm dòng này
    ];

    public function dangKy(): BelongsTo
    {
        return $this->belongsTo(DangKy::class);
    }

    public function lichHoc(): BelongsTo
    {
        return $this->belongsTo(LichHoc::class);
    }
}
