<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory; // Bỏ comment nếu cần

// Import model liên quan
use App\Models\HocVien;
use App\Models\KhoaHoc;
// Nếu có bảng ket_qua_khoa_hocs liên kết với dang_ky_id
// use App\Models\KetQuaKhoaHoc;
// Nếu có bảng diem_danh_buoi_hocs liên kết với dang_ky_id
// use App\Models\DiemDanhBuoiHoc;

class DangKy extends Model
{
    // use HasFactory; // Bỏ comment nếu cần

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array<string>
     */
    protected $fillable = [
        'hoc_vien_id',
        'khoa_hoc_id',
        // Thêm các cột khác nếu có trong bảng dang_kies và cần fillable
    ];

    /**
     * Các thuộc tính sẽ được cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime',
    ];

    // --- Bắt đầu: Các phương thức quan hệ ---

    /**
     * Một Đăng ký thuộc về một Học viên.
     */
    public function hocVien()
    {
        return $this->belongsTo(HocVien::class, 'hoc_vien_id');
    }

    /**
     * Một Đăng ký thuộc về một Khóa học.
     */
    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    /**
     * Một Đăng ký có thể có một Kết quả khóa học.
     * (Giả định có bảng ket_qua_khoa_hocs với khóa ngoại dang_ky_id)
     */
    // public function ketQuaKhoaHoc()
    // {
    //     return $this->hasOne(KetQuaKhoaHoc::class, 'dang_ky_id');
    // }

    /**
     * Một Đăng ký có thể có nhiều Điểm danh buổi học.
     * (Giả định có bảng diem_danh_buoi_hocs với khóa ngoại dang_ky_id)
     */
    // public function diemDanhBuoiHocs()
    // {
    //     return $this->hasMany(DiemDanhBuoiHoc::class, 'dang_ky_id');
    // }

    // --- Kết thúc: Các phương thức quan hệ ---
}
