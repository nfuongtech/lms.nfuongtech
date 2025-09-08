<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonVi extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ma_don_vi',
        'thaco_tdtv',
        'cong_ty_ban_nvqt',
        'phong_bo_phan',
        'noi_lam_viec_chi_tiet',
    ];

    /**
     * Get all of the hocViens for the DonVi.
     */
    public function hocViens(): HasMany
    {
        return $this->hasMany(HocVien::class);
    }

    /**
     * Accessor để tạo cột ảo "Tên hiển thị".
     * This combines parts of the unit's hierarchy into a single display string.
     */
    protected function tenHienThi(): Attribute
    {
        return Attribute::make(
            get: function () {
                return collect([
                    $this->phong_bo_phan,
                    $this->cong_ty_ban_nvqt,
                    $this->thaco_tdtv,
                ])->filter()->implode(', '); // Lọc bỏ các giá trị rỗng và nối chuỗi
            }
        );
    }
}
