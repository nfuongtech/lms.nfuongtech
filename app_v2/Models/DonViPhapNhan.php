<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonViPhapNhan extends Model
{
    use HasFactory;

    protected $primaryKey = 'ma_so_thue'; // Khai báo khóa chính
    public $incrementing = false; // Khóa chính không phải là số tự tăng
    protected $keyType = 'string'; // Kiểu dữ liệu của khóa chính

    protected $fillable = [
        'ma_so_thue',
        'ten_don_vi',
        'dia_chi',
        'ghi_chu',
    ];

    public function hocViens(): HasMany
    {
        // Một đơn vị pháp nhân có nhiều học viên
        return $this->hasMany(HocVien::class, 'don_vi_phap_nhan_id', 'ma_so_thue');
    }
}
