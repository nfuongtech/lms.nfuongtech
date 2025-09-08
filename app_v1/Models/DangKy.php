<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DangKy extends Model
{
    use HasFactory;

    protected $fillable = [
        'hoc_vien_id',
        'khoa_hoc_id',
        'ngay_dang_ky',
        'trang_thai', // pending|approved|rejected...
    ];

    public function hocVien() { return $this->belongsTo(HocVien::class); }
    public function khoaHoc() { return $this->belongsTo(KhoaHoc::class); }

    public function ketQuaKhoaHoc() { return $this->hasOne(KetQuaKhoaHoc::class, 'dang_ky_id'); }

    public function ketQuaChuyenDes()
    {
        return $this->hasManyThrough(
            KetQuaChuyenDe::class,
            KetQuaKhoaHoc::class,
            'dang_ky_id',
            'ket_qua_khoa_hoc_id',
            'id',
            'id'
        );
    }
}
