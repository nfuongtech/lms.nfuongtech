<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChuyenDe extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
// Thêm vào trong class ChuyenDe
    public function giangViens(): BelongsToMany
    {
        return $this->belongsToMany(GiangVien::class, 'chuyen_de_giang_vien');
    }
    protected $fillable = [
        'ma_so',
        'ten_chuyen_de',
        'thoi_luong',
        'doi_tuong_dao_tao',
        'muc_tieu',
        'noi_dung',
    ];
}
