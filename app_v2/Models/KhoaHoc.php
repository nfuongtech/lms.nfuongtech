<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhoaHoc extends Model
{
    use HasFactory;

    protected $fillable = [
        'chuyen_de_id',
        'ma_khoa',
        'ten_khoa_hoc',
        'nam',
        'trang_thai',
    ];

    public function chuyenDe()
    {
        return $this->belongsTo(ChuyenDe::class, 'chuyen_de_id');
    }

    public function lichHocs()
    {
        return $this->hasMany(LichHoc::class, 'khoa_hoc_id');
    }
}
