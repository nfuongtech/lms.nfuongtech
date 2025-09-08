<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'tinh_trang',
    ];

    public function chuyenDes(): BelongsToMany
    {
        return $this->belongsToMany(ChuyenDe::class, 'chuyen_de_giang_vien', 'giang_vien_id', 'chuyen_de_id');
    }
}
