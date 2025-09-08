<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChuyenDe extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'bai_giang_path' => 'array', // Ép kiểu cho trường lưu nhiều file
    ];

    /**
     * The giangViens that belong to the ChuyenDe.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function giangViens(): BelongsToMany
    {
        return $this->belongsToMany(GiangVien::class, 'chuyen_de_giang_vien');
    }
    public function chuongTrinhs(): BelongsToMany
    {
        return $this->belongsToMany(ChuongTrinh::class, 'chuong_trinh_chuyen_de');
    }
}
