<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuyTacMaKhoa extends Model
{
    use HasFactory;

    protected $fillable = [
        'loai_hinh_dao_tao',
        'tien_to',
        'dinh_dang',
        'mau_so'
    ];

    protected $casts = [
        'mau_so' => 'integer'
    ];

    /**
     * Tạo mã khóa học tự động theo quy tắc
     */
    public static function taoMaKhoaHoc($loaiHinhDaoTao)
    {
        $quyTac = self::where('loai_hinh_dao_tao', $loaiHinhDaoTao)->first();
        
        if (!$quyTac) {
            throw new \Exception("Không tìm thấy quy tắc mã khóa cho loại hình đào tạo: {$loaiHinhDaoTao}");
        }

        $soThuTu = $quyTac->mau_so + 1;
        $quyTac->update(['mau_so' => $soThuTu]);

        $nam = date('y');
        $thang = date('m');
        $soThuTuFormatted = str_pad($soThuTu, 3, '0', STR_PAD_LEFT);

        return "{$quyTac->tien_to}-{$nam}{$thang}{$soThuTuFormatted}";
    }

    /**
     * Lấy tất cả các loại hình đào tạo để hiển thị trong select
     */
    public static function getLoaiHinhDaoTaoOptions()
    {
        return self::pluck('loai_hinh_dao_tao', 'loai_hinh_dao_tao')->toArray();
    }
}
