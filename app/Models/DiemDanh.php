<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiemDanh extends Model
{
    protected $table = 'diem_danhs';

    protected $fillable = [
        'dang_ky_id',
        'lich_hoc_id',
        'trang_thai',          // Có mặt / Vắng phép / Vắng không phép
        'ly_do_vang',          // lý do nếu vắng
        'diem_buoi_hoc',       // điểm buổi học
        'so_gio_hoc',          // số giờ thực học
        'danh_gia_ky_luat',    // đánh giá kỷ luật
    ];

    public function dangKy(): BelongsTo
    {
        return $this->belongsTo(DangKy::class, 'dang_ky_id');
    }

    public function lichHoc(): BelongsTo
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }
}
