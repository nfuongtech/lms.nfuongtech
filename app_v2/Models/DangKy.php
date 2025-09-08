<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DangKy extends Model
{
    use HasFactory;

    protected $table = 'dang_kys';

    protected $fillable = [
        'hoc_vien_id',
        'khoa_hoc_id',
    ];

    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function hocVien()
    {
        return $this->belongsTo(HocVien::class, 'hoc_vien_id');
    }
}
