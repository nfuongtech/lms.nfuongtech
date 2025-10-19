<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonVi extends Model
{
    use HasFactory;

    protected $table = 'don_vis';

    protected $fillable = [
        'ma_don_vi',
        'thaco_tdtv',
        'cong_ty_ban_nvqt',
        'phong_bo_phan',
        'noi_lam_viec_chi_tiet',
        'ten_hien_thi',
    ];

    public function hocViens()
    {
        return $this->hasMany(HocVien::class, 'don_vi_id');
    }

    protected static function booted()
    {
        static::creating(function ($donVi) {
            // Tạo mã đơn vị tự động nếu chưa có
            if (!$donVi->ma_don_vi) {
                $today = now()->format('Ymd');
                $last = DonVi::where('ma_don_vi', 'like', "{$today}-%")->latest('id')->first();
                $num = $last ? intval(substr($last->ma_don_vi, -3)) + 1 : 1;
                $donVi->ma_don_vi = $today . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function ($donVi) {
            // Tạo tên hiển thị
            $parts = array_filter([
                $donVi->thaco_tdtv,
                $donVi->cong_ty_ban_nvqt,
                $donVi->phong_bo_phan,
            ]);
            $donVi->ten_hien_thi = implode(' - ', $parts);
        });
    }
}
