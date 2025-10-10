<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonViPhapNhan extends Model
{
    use HasFactory;

    protected $table = 'don_vi_phap_nhans';

    protected $primaryKey = 'ma_so_thue';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ma_so_thue',
        'ten_don_vi',
        'dia_chi',
        'ghi_chu',
    ];

    public function hocViens()
    {
        return $this->hasMany(HocVien::class, 'don_vi_phap_nhan_id', 'ma_so_thue');
    }
}
