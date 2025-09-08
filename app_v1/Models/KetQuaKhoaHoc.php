<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KetQuaKhoaHoc extends Model
{
    use HasFactory;

    protected $table = 'ket_qua_khoa_hocs'; // bảng hiện có

    protected $fillable = [
        'dang_ky_id',
        'co_mat',
        'ly_do_vang',
        'diem',        // điểm tổng kết khóa (nếu dùng)
        'ket_qua',     // trạng thái cuối
        'can_hoc_lai',
        'hoc_phi',
        'nguoi_nhap',
        'ngay_nhap',
    ];

    protected $casts = [
        'co_mat' => 'boolean',
        'diem' => 'decimal:2',
        'hoc_phi' => 'integer',
        'ngay_nhap' => 'datetime',
    ];

    // Quan hệ tới DangKy
    public function dangKy()
    {
        return $this->belongsTo(DangKy::class, 'dang_ky_id');
    }

    // Qua DangKy -> HocVien: $ketQua->dangKy->hocVien
    // Quan hệ 1-n tới chi tiết chuyên đề
    public function chiTietChuyenDes()
    {
        return $this->hasMany(KetQuaChuyenDe::class, 'ket_qua_khoa_hoc_id');
    }
}
