<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LichHoc;

class KhoaHoc extends Model
{
    use HasFactory;

    protected $fillable = [
        'ma_khoa_hoc',
        'ten_khoa_hoc',
        'trang_thai',
    ];

    // Quan hệ 1 khóa học có nhiều lịch học
    public function lich_hocs()
    {
        return $this->hasMany(LichHoc::class, 'khoa_hoc_id', 'id');
    }
}
