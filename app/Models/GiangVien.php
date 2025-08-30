<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GiangVien extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ma_so',
        'ho_ten',
        'hinh_anh_path',
        'gioi_tinh',
        'nam_sinh',
        'don_vi',
        'ho_khau_noi_lam_viec',
        'trinh_do',
        'chuyen_mon',
        'so_nam_kinh_nghiem',
        'tom_tat_kinh_nghiem',
    ];

    protected $casts = [
        'nam_sinh' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chuyenDes(): BelongsToMany
    {
        return $this->belongsToMany(ChuyenDe::class, 'chuyen_de_giang_vien');
    }
}
