<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;

    class ChuongTrinh extends Model
    {
        use HasFactory;

        protected $fillable = [
            'ma_chuong_trinh',
            'ten_chuong_trinh',
            'thoi_luong',
            'muc_tieu_dao_tao',
            'loai_hinh_dao_tao',
            'tinh_trang',
        ];

        public function chuyenDes(): BelongsToMany
        {
            return $this->belongsToMany(ChuyenDe::class, 'chuong_trinh_chuyen_de');
        }
    }
