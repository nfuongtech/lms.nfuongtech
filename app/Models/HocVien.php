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
        'sdt', // ⚡ thêm vào fillable
        'ngay_vao',
        'chuc_vu',
        'don_vi_id',
        'don_vi_phap_nhan_id',
        'tinh_trang',
        'hinh_anh_path',
    ];

    protected $casts = [
        'nam_sinh' => 'date',
        'ngay_vao' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($hocVien) {
            $hocVien->msnv ??= self::generateMSNV();
            $hocVien->tinh_trang ??= 'Đang làm việc';
            $hocVien->sdt = self::normalizePhone($hocVien->sdt);
        });

        static::updating(function ($hocVien) {
            $hocVien->sdt = self::normalizePhone($hocVien->sdt);
        });
    }

    /**
     * Chuẩn hóa số điện thoại
     */
    private static function normalizePhone(?string $sdt): ?string
    {
        if (empty($sdt)) {
            return null;
        }

        $sdt = trim($sdt);

        // Nếu bắt đầu bằng dấu "+" (số quốc tế) → giữ nguyên dấu "+"
        if (str_starts_with($sdt, '+')) {
            // Bỏ khoảng trắng, dấu chấm, gạch ngang nhưng giữ "+"
            return '+' . preg_replace('/[^\d]/', '', substr($sdt, 1));
        }

        // Không có "+": chỉ giữ số
        $sdt = preg_replace('/\D/', '', $sdt);

        // Nếu bắt đầu bằng "84" thì đổi thành "0"
        if (str_starts_with($sdt, '84') && strlen($sdt) > 2) {
            $sdt = '0' . substr($sdt, 2);
        }

        return $sdt;
    }

    /**
     * Sinh mã học viên tự động HV-YYMMXXX
     */
    private static function generateMSNV(): string
    {
        $prefix = 'HV-' . now()->format('ym');
        $last = self::where('msnv', 'like', $prefix . '%')
            ->orderBy('msnv', 'desc')
            ->first();

        if ($last && preg_match('/(\d{3})$/', $last->msnv, $m)) {
            $num = intval($m[1]) + 1;
        } else {
            $num = 1;
        }

        $num = min($num, 999);
        return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    public function donViPhapNhan()
    {
        return $this->belongsTo(DonViPhapNhan::class, 'don_vi_phap_nhan_id', 'ma_so_thue');
    }

    public function donVi()
    {
        return $this->belongsTo(DonVi::class, 'don_vi_id');
    }

    public function dangKies()
    {
        return $this->hasMany(DangKy::class, 'hoc_vien_id');
    }
}
