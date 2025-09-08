<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KetQuaKhoaHoc extends Model
{
    use HasFactory;

    protected $fillable = [
        'hoc_vien_id',
        'khoa_hoc_id',
        'diem',
        'xep_loai',
        'nhan_xet',
    ];

    public function hocVien()
    {
        return $this->belongsTo(HocVien::class, 'hoc_vien_id');
    }

    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }
}
