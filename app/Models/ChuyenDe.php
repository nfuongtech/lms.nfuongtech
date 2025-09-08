<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChuyenDe extends Model
{
    use HasFactory;

    protected $fillable = [
        'ma_so',
        'ten_chuyen_de',
        'thoi_luong',
        'doi_tuong_dao_tao',
        'muc_tieu',
        'noi_dung',
        'trang_thai_tai_lieu',
        'bai_giang_path',
    ];

    protected $casts = [
        'bai_giang_path' => 'array',
    ];

    public function giangViens(): BelongsToMany
    {
        return $this->belongsToMany(GiangVien::class, 'chuyen_de_giang_vien');
    }

    public function chuongTrinhs(): BelongsToMany
    {
        return $this->belongsToMany(ChuongTrinh::class, 'chuong_trinh_chuyen_de');
    }

    // <-- thêm relation này để truy vấn lich_hocs theo chuyen_de
    public function lichHocs(): HasMany
    {
        return $this->hasMany(LichHoc::class, 'chuyen_de_id');
    }
}
