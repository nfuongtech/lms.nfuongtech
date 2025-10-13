<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class HocVienHoanThanh extends Model
{
    protected static ?string $resolvedTable = null;

    protected $fillable = [
        'hoc_vien_id',
        'khoa_hoc_id',
        'ket_qua_khoa_hoc_id',
        'ngay_hoan_thanh',
        'chi_phi_dao_tao',
        'chung_chi_link',
        'chung_chi_tap_tin',
        'thoi_han_chung_nhan',
        'ngay_het_han_chung_nhan',
        'chung_chi_da_cap',
        'ghi_chu',
    ];

    protected $casts = [
        'ngay_hoan_thanh' => 'date',
        'chung_chi_da_cap' => 'boolean',
        'chi_phi_dao_tao' => 'decimal:2',
        'ngay_het_han_chung_nhan' => 'date',
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

    public function getTable()
    {
        if (static::$resolvedTable) {
            return static::$resolvedTable;
        }

        $defaultTable = parent::getTable();
        $candidates = [$defaultTable];

        if (! in_array('hoc_vien_hoan_thanh', $candidates, true)) {
            $candidates[] = 'hoc_vien_hoan_thanh';
        }

        foreach ($candidates as $candidate) {
            if (Schema::hasTable($candidate)) {
                return static::$resolvedTable = $candidate;
            }
        }

        return $defaultTable;
    }
}
