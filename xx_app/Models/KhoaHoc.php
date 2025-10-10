<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KhoaHoc extends Model
{
    protected $table = 'khoa_hocs';

    protected $fillable = [
        'chuong_trinh_id',
        'ma_khoa_hoc',
        'ten_khoa_hoc',
        'nam',
        'trang_thai',
        'yeu_cau_phan_tram_gio',
        'yeu_cau_diem_tb',
        'tam_hoan',
        'ly_do_tam_hoan',
    ];

    protected $casts = [
        'tam_hoan' => 'boolean',
    ];

    public function chuongTrinh(): BelongsTo
    {
        return $this->belongsTo(ChuongTrinh::class, 'chuong_trinh_id');
    }

    public function lichHocs(): HasMany
    {
        return $this->hasMany(LichHoc::class, 'khoa_hoc_id');
    }

    // để tránh lỗi nếu nơi khác gọi
    public function dangKies(): HasMany
    {
        return $this->hasMany(DangKy::class, 'khoa_hoc_id');
    }

    /** Suy luận trạng thái runtime (ưu tiên tạm hoãn) */
    public function getTrangThaiHienThiAttribute(): string
    {
        if ($this->tam_hoan) return 'Tạm hoãn';

        $this->loadMissing('lichHocs');
        if ($this->lichHocs->isEmpty()) {
            return $this->trang_thai ?? 'Dự thảo';
        }

        $start = $this->lichHocs->map(fn ($l) => $l->getStartDateTime())->filter()->min();
        $end   = $this->lichHocs->map(fn ($l) => $l->getEndDateTime())->filter()->max();

        if ($start && $end) {
            $now = Carbon::now();
            if ($now->lt($start))            return 'Ban hành';
            if ($now->between($start, $end)) return 'Đang đào tạo';
            if ($now->gt($end))              return 'Kết thúc';
        }

        return $this->trang_thai ?? 'Dự thảo';
    }
}
