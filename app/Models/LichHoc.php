<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LichHoc extends Model
{
    use HasFactory;

    protected $table = 'lich_hocs';

    /**
     * Những cột được phép gán
     */
    protected $fillable = [
        'khoa_hoc_id',
        'chuyen_de_id',
        'giang_vien_id',
        'ngay_hoc',
        'buoi',          // số buổi/phiên (int)
        'gio_bat_dau',
        'gio_ket_thuc',
        'dia_diem',
        'tuan',
        'thang',
        'nam',
        'ghi_chu',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'ngay_hoc' => 'date',
        'buoi' => 'integer',
        'tuan' => 'integer',
        'thang' => 'integer',
        'nam' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Quan hệ: Lịch thuộc về 1 KhoaHoc
     */
    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    /**
     * Quan hệ: Lịch thuộc về 1 ChuyenDe (có thể null)
     */
    public function chuyenDe(): BelongsTo
    {
        return $this->belongsTo(ChuyenDe::class, 'chuyen_de_id');
    }

    /**
     * Quan hệ: Lịch có 1 GiangVien (có thể null)
     */
    public function giangVien(): BelongsTo
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    /**
     * Quan hệ: Lịch có nhiều DiemDanh
     */
    public function diemDanhs(): HasMany
    {
        return $this->hasMany(DiemDanh::class, 'lich_hoc_id');
    }

    /**
     * Scope: lọc theo tuần & năm
     */
    public function scopeOfWeekYear($query, int $tuan, int $nam)
    {
        return $query->where('tuan', $tuan)->where('nam', $nam);
    }

    /**
     * Scope: lọc theo tháng & năm
     */
    public function scopeOfMonthYear($query, int $thang, int $nam)
    {
        return $query->where('thang', $thang)->where('nam', $nam);
    }

    /**
     * Helper display: "Tên chuyên đề - Ngày (Giảng viên)"
     */
    public function getDisplayAttribute(): string
    {
        $chuyenDe = $this->chuyenDe ? $this->chuyenDe->ten_chuyen_de : null;
        $gv = $this->giangVien ? $this->giangVien->ho_ten : null;
        $ngay = $this->ngay_hoc ? $this->ngay_hoc->format('Y-m-d') : ($this->ngay_hoc ?? '');
        $parts = array_filter([$chuyenDe, $ngay, $gv]);
        return implode(' - ', $parts);
    }
}
