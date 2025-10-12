<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KetQuaKhoaHoc extends Model
{
    use HasFactory;

    protected $table = 'ket_qua_khoa_hocs';

    protected $fillable = [
        'dang_ky_id',
        'tong_so_gio_ke_hoach',
        'tong_so_gio_thuc_te',
        'diem_trung_binh',
        'ket_qua_goi_y',
        'ket_qua',
        'danh_gia_ren_luyen',
        'can_hoc_lai',
        'hoc_phi',
        'nguoi_nhap',
        'ngay_nhap',
        'needs_review',
    ];

    protected $casts = [
        'tong_so_gio_ke_hoach' => 'decimal:2',
        'tong_so_gio_thuc_te'  => 'decimal:2',
        'diem_trung_binh'      => 'decimal:2',
        'can_hoc_lai'          => 'boolean',
        'needs_review'         => 'boolean',
        'ngay_nhap'            => 'datetime',
    ];

    public function dangKy()
    {
        return $this->belongsTo(DangKy::class, 'dang_ky_id');
    }
}
