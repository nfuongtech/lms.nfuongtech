<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HocVien extends Model
{
    use HasFactory;

    protected $fillable = [
        'msnv',
        'ho_ten',
        'gioi_tinh',
        'nam_sinh',
        'ngay_vao',
        'chuc_vu',
        'don_vi_id',
        'don_vi_phap_nhan_id',
        'email',
        'sdt', // Thêm trường mới
        'hinh_anh_path',
        'tinh_trang',
    ];

    public function donVi(): BelongsTo
    {
        return $this->belongsTo(DonVi::class);
    }

    public function donViPhapNhan(): BelongsTo
    {
        return $this->belongsTo(DonViPhapNhan::class, 'don_vi_phap_nhan_id', 'ma_so_thue');
    }

    public function dangKys(): HasMany
    {
        return $this->hasMany(DangKy::class);
    }
}
