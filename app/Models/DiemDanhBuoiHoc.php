<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiemDanhBuoiHoc extends Model
{
    protected $fillable = [
        'dang_ky_id',
        'lich_hoc_id',
        'trang_thai',
        'ly_do_vang',
        'diem_buoi_hoc',
    ];

    protected $casts = [
        'trang_thai' => 'string',
    ];

    public function dangKy()
    {
        return $this->belongsTo(DangKy::class);
    }

    public function lichHoc()
    {
        return $this->belongsTo(LichHoc::class);
    }
}
