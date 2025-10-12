<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HocVienHoanThanh extends Model
{
    protected $table = 'hoc_vien_hoan_thanh';

    protected $fillable = [
        'hoc_vien_id',
        'khoa_hoc_id',
        'ket_qua_khoa_hoc_id',
        'ngay_hoan_thanh',
        'chi_phi_dao_tao',
        'chung_chi_link',
        'chung_chi_tap_tin',
        'chung_chi_da_cap',
        'ghi_chu',
        'da_duyet',
        'ngay_duyet',
        'so_chung_nhan',
        'chung_chi_het_han',
    ];

    protected $casts = [
        'ngay_hoan_thanh' => 'date',
        'chung_chi_da_cap' => 'boolean',
        'chi_phi_dao_tao' => 'decimal:2',
        'da_duyet' => 'boolean',
        'ngay_duyet' => 'datetime',
        'chung_chi_het_han' => 'date',
    ];

    public function hocVien()
    {
        return $this->belongsTo(HocVien::class, 'hoc_vien_id');
    }

    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function ketQua()
    {
        return $this->belongsTo(KetQuaKhoaHoc::class, 'ket_qua_khoa_hoc_id');
    }
}
