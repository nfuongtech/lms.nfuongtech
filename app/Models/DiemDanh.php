<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiemDanh extends Model
{
    protected $table = 'diem_danhs'; // trỏ đúng bảng trong DB

    protected $fillable = [
        'dang_ky_id',
        'lich_hoc_id',
        'trang_thai',          // có mặt / vắng phép / vắng không phép
        'ly_do_vang',          // lý do nếu vắng
        'diem_buoi_hoc',       // điểm buổi học (nếu có)
        'danh_gia_ren_luyen',  // đánh giá kỷ luật
    ];

    /**
     * Quan hệ: Một bản điểm danh thuộc về 1 đăng ký học viên
     */
    public function dangKy(): BelongsTo
    {
        return $this->belongsTo(DangKy::class, 'dang_ky_id');
    }

    /**
     * Quan hệ: Một bản điểm danh thuộc về 1 buổi học (lịch học)
     */
    public function lichHoc(): BelongsTo
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }
}
