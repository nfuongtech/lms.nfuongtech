<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChuyenDe extends Model
{
    protected $fillable = [
        'ten_chuyen_de',
        'thoi_luong',
        'ma_so',
        'dang_ky_id',
    ];

    // Quan hệ với bảng dang_kys
    public function dangKy()
    {
        return $this->belongsTo(DangKy::class, 'dang_ky_id');
    }
}
