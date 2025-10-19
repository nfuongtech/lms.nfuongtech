<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class HocVienKhongHoanThanh extends Model
{
    protected static ?string $resolvedTable = null;

    protected $fillable = [
        'hoc_vien_id',
        'khoa_hoc_id',
        'ket_qua_khoa_hoc_id',
        'ly_do_khong_hoan_thanh',
        'co_the_ghi_danh_lai',
    ];

    protected $casts = [
        'co_the_ghi_danh_lai' => 'boolean',
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

        if (! in_array('hoc_vien_khong_hoan_thanh', $candidates, true)) {
            $candidates[] = 'hoc_vien_khong_hoan_thanh';
        }

        foreach ($candidates as $candidate) {
            if (Schema::hasTable($candidate)) {
                return static::$resolvedTable = $candidate;
            }
        }

        return $defaultTable;
    }
}
