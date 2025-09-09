<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HocVien extends Model
{
    use HasFactory;

    protected $fillable = [
        'msnv',
        'ho_ten',
        'gioi_tinh',
        'nam_sinh',
        'email',
        'ngay_vao',
        'chuc_vu',
        'don_vi_id',
        'tinh_trang',
        'hinh_anh_path',
    ];

    protected $casts = [
        'nam_sinh' => 'date',
        'ngay_vao' => 'date',
    ];

    // --- Bắt đầu: Tự sinh MSNV nếu để trống ---
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($hocVien) {
            if (empty($hocVien->msnv)) {
                // Định dạng mong muốn: HV-YYMMDDXX
                $prefix = 'TT-' . now()->format('ymd'); // Ví dụ: HV-250405
                $fullPrefix = $prefix . '%'; // Để tìm kiếm LIKE 'HV-250405%'

                // Tìm bản ghi cuối cùng trong ngày với định dạng này
                $lastHocVien = static::where('msnv', 'like', $fullPrefix)
                    ->orderBy('msnv', 'desc')
                    ->first();

                if ($lastHocVien) {
                    // Trích xuất phần số cuối cùng (XX)
                    // Ví dụ: MSNV là 'HV-25040503', phần số là '03'
                    $lastPart = substr($lastHocVien->msnv, -2); // Lấy 2 ký tự cuối
                    $lastNumber = intval($lastPart);

                    // Tăng số lên 1, đảm bảo có 2 chữ số
                    $newNumber = str_pad(min($lastNumber + 1, 99), 2, '0', STR_PAD_LEFT);
                } else {
                    // Nếu chưa có bản ghi nào trong ngày, bắt đầu từ 01
                    $newNumber = '01';
                }

                $hocVien->msnv = $prefix . $newNumber; // Ví dụ: HV-25040501
            }
        });
    }
    // --- Kết thúc: Tự sinh MSNV nếu để trống ---

    public function donVi()
    {
        return $this->belongsTo(DonVi::class, 'don_vi_id');
    }
}
