<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GiangVien extends Model
{
    use HasFactory;

    protected $table = 'giang_viens';

    protected $fillable = [
        'user_id',
        'ma_so',
        'ho_ten',
        'email',
        'dien_thoai',
        'hinh_anh_path',
        'gioi_tinh',
        'nam_sinh',
        'don_vi',
        'ho_khau_noi_lam_viec',
        'trinh_do',
        'chuyen_mon',
        'so_nam_kinh_nghiem',
        'tom_tat_kinh_nghiem',
        'tinh_trang',
    ];

    public function chuyenDes(): BelongsToMany
    {
        return $this->belongsToMany(
            ChuyenDe::class,
            'chuyen_de_giang_vien',
            'giang_vien_id',
            'chuyen_de_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        // Khi xóa Giảng viên => xóa luôn User liên quan (nếu có)
        static::deleting(function ($giangVien) {
            if ($giangVien->user) {
                // xóa user liên quan
                $giangVien->user->delete();
            }
        });
    }
}
