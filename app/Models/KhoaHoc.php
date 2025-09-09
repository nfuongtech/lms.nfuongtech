<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; // Hoặc \Illuminate\Database\Eloquent\Factories\HasFactory; nếu bạn dùng factory
// use Illuminate\Database\Eloquent\Factories\HasFactory; // Bỏ comment nếu cần

// Import model liên quan
use App\Models\ChuongTrinh;
use App\Models\DangKy; // Cần import để dùng trong quan hệ
use App\Models\HocVien;
use App\Models\LichHoc; // Nếu có quan hệ với lich_hocs

class KhoaHoc extends Model
{
    // use HasFactory; // Bỏ comment nếu cần

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array<string>
     */
    protected $fillable = [
        'chuong_trinh_id',
        'ma_khoa_hoc',
        'nam',
        'trang_thai', // Ví dụ: Soạn thảo, Kế hoạch, Ban hành, Đang đào tạo, Kết thúc
        'ghi_chu',
        // Thêm các cột khác nếu có và cần fillable
    ];

    /**
     * Các thuộc tính sẽ được cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'nam' => 'integer', // Nếu bạn muốn cast nam thành integer
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime',
    ];

    // --- Bắt đầu: Các phương thức quan hệ ---

    /**
     * Một Khóa học thuộc về một Chương trình.
     */
    public function chuongTrinh()
    {
        return $this->belongsTo(ChuongTrinh::class, 'chuong_trinh_id');
    }

    /**
     * Một Khóa học có nhiều Đăng ký.
     * Đây là phương thức được gọi trong lỗi, cần được định nghĩa chính xác.
     */
    public function dangKys() // Tên phương thức phải là 'dangKys' để khớp với cách gọi $khoaHoc->dangKys
    {
        return $this->hasMany(DangKy::class, 'khoa_hoc_id');
    }

    /**
     * Một Khóa học có nhiều Lịch học.
     */
    public function lichHocs()
    {
        return $this->hasMany(LichHoc::class, 'khoa_hoc_id');
    }

    /**
     * Một Khóa học có nhiều Học viên thông qua bảng trung gian dang_kies.
     * Quan hệ Many-to-Many.
     */
    public function hocViens()
    {
        return $this->belongsToMany(HocVien::class, 'dang_kies', 'khoa_hoc_id', 'hoc_vien_id');
    }

    /**
     * Đếm số lượng đăng ký.
     * Phương thức accessor để dùng với withCount hoặc gọi trực tiếp.
     * Ví dụ: $khoaHoc->so_luong_hoc_vien
     */
    public function getSoLuongHocVienAttribute()
    {
        return $this->dangKys()->count();
    }

    // --- Kết thúc: Các phương thức quan hệ ---
}
