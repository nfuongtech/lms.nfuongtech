<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HocVien extends Model
{
    use HasFactory;

    protected $table = 'hoc_viens';

    protected $fillable = [
        'msnv',
        'ho_ten',
        'email',
        'so_dien_thoai',
        'chuc_vu',
        'don_vi_id',
        'tinh_trang',
        'hinh_anh_path',
        'nam_sinh',
        'gioi_tinh',
        'ngay_vao',
    ];

    public function donVi()
    {
        return $this->belongsTo(DonVi::class, 'don_vi_id');
    }

    public function dangKys()
    {
        return $this->hasMany(DangKy::class, 'hoc_vien_id');
    }

    /**
     * Tự động sinh MSNV khi creating nếu msnv rỗng.
     * Đảm bảo sinh duy nhất theo ngày: YYYYMMDD-XXX
     */
    protected static function booted()
    {
        static::creating(function (HocVien $hv) {
            if (empty($hv->msnv)) {
                $date = now()->format('Ymd');

                // Lấy số lớn nhất hiện có cho ngày này (kết quả là int)
                // Note: sử dụng DB raw để parse cuối chuỗi sau dấu '-' nếu format đúng.
                $last = DB::table('hoc_viens')
                    ->selectRaw("MAX(CAST(SUBSTRING_INDEX(msnv, '-', -1) AS UNSIGNED)) as max_seq")
                    ->where('msnv', 'like', "{$date}-%")
                    ->first();

                $lastSeq = $last->max_seq ? intval($last->max_seq) : 0;
                $newSeq = $lastSeq + 1;
                $hv->msnv = $date . '-' . str_pad($newSeq, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
