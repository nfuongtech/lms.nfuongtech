<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiangVien extends Model
{
    use HasFactory;

    protected $fillable = ['ma_so','ho_ten','email','tinh_trang'];

    public function chuyenDes()
    {
        return $this->belongsToMany(ChuyenDe::class, 'chuyen_de_giang_vien', 'giang_vien_id', 'chuyen_de_id');
    }
}
