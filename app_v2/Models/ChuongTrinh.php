<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChuongTrinh extends Model
{
    use HasFactory;

    protected $fillable = ['ma_chuong_trinh','ten_chuong_trinh','thoi_luong','loai_hinh_dao_tao','tinh_trang'];

    public function chuyenDes()
    {
        return $this->hasMany(ChuyenDe::class, 'chuong_trinh_id');
    }
}
