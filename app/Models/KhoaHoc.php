<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhoaHoc extends Model
{
    use HasFactory;

    protected $table = 'khoa_hocs';

    protected $fillable = [
        'ten_khoa_hoc',
        'ma_khoa_hoc',
        'chuong_trinh_id',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'trang_thai',
        'ghi_chu',
        // thêm các cột khác nếu cần
    ];

    protected $casts = [
        'ngay_bat_dau' => 'date',
        'ngay_ket_thuc' => 'date',
    ];

    /* === Quan hệ === */
    public function chuongTrinh()
    {
        return $this->belongsTo(ChuongTrinh::class, 'chuong_trinh_id');
    }

    public function lichHocs()
    {
        return $this->hasMany(LichHoc::class, 'khoa_hoc_id');
    }

    public function dangKies()
    {
        return $this->hasMany(DangKy::class, 'khoa_hoc_id');
    }

    public function hocViens()
    {
        // Lấy học viên qua bảng dang_kies (dang_kies.hoc_vien_id)
        return $this->hasManyThrough(
            HocVien::class,
            DangKy::class,
            'khoa_hoc_id', // FK on DangKy
            'id',          // PK on HocVien
            'id',          // PK on KhoaHoc
            'hoc_vien_id'  // FK on DangKy that references HocVien
        );
    }

    public function edits()
    {
        return $this->hasMany(KhoaHocEdit::class, 'khoa_hoc_id');
    }

    /* === Ghi lịch chỉnh sửa (auto) === */
    protected static function booted()
    {
        static::updating(function ($model) {
            // Lấy thay đổi (tránh push bảng timestamps)
            $original = $model->getOriginal();
            $changes = array_diff_assoc($model->getAttributes(), $original);
            unset($changes['updated_at'], $changes['created_at']);

            if (!empty($changes)) {
                // Lưu lịch sử chỉnh sửa
                KhoaHocEdit::create([
                    'khoa_hoc_id' => $model->id,
                    'user_id' => auth()->id() ?? null,
                    'changes' => $changes,
                ]);
            }
        });

        static::created(function ($model) {
            // Nếu cần làm cache count hay hành động khác sau khi tạo
        });
    }
}
