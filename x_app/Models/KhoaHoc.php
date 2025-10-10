<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class KhoaHoc extends Model
{
    use HasFactory;

    protected $table = 'khoa_hocs';
    protected $guarded = [];

    protected $casts = [
        'tam_hoan' => 'boolean',
        'nam'      => 'integer',
    ];

    public function chuongTrinh()
    {
        return $this->belongsTo(ChuongTrinh::class, 'chuong_trinh_id');
    }

    public function lichHocs()
    {
        return $this->hasMany(LichHoc::class, 'khoa_hoc_id');
    }

    /** Chuẩn hoá chuỗi giờ về H:i:s và tách H/M/S số nguyên */
    private function splitHms(?string $time, string $fallback = '00:00:00'): array
    {
        $t = trim((string) $time);
        if ($t === '') $t = $fallback;
        // chấp nhận '7:30' hoặc '07:30' => thêm :00
        if (\strlen($t) === 4 || \strlen($t) === 5) {
            $t .= ':00';
        }
        [$h, $m, $s] = array_map('intval', explode(':', $t));
        return [$h, $m, $s];
    }

    /**
     * Trạng thái thông minh theo yêu cầu:
     * - Tạm hoãn > mọi thứ
     * - 0 lịch      => Dự thảo
     * - 1 lịch      => now ∈ [start,end] của LỊCH ĐÓ  => Đang đào tạo; now < start => Ban hành; now > end => Kết thúc
     * - ≥2 lịch     => giai đoạn [minStart, maxEnd] (kể cả có khoảng trống) => now trong khoảng => Đang đào tạo
     */
    public function computeTrangThai(): string
    {
        if ($this->tam_hoan) {
            return 'Tạm hoãn';
        }

        $items = $this->lichHocs()->select('ngay_hoc','gio_bat_dau','gio_ket_thuc')->get();
        $count = $items->count();

        if ($count === 0) {
            return 'Dự thảo';
        }

        $intervals = [];
        foreach ($items as $lh) {
            // ngày
            $day = $lh->ngay_hoc instanceof \DateTimeInterface
                ? Carbon::instance($lh->ngay_hoc)->startOfDay()
                : Carbon::parse($lh->ngay_hoc)->startOfDay();

            // giờ bắt đầu/kết thúc (chấp nhận 'H:i' hoặc 'H:i:s')
            [$sh, $sm, $ss] = $this->splitHms($lh->gio_bat_dau, '00:00:00');
            [$eh, $em, $es] = $this->splitHms($lh->gio_ket_thuc, '23:59:59');

            $start = (clone $day)->setTime($sh, $sm, $ss);
            $end   = (clone $day)->setTime($eh, $em, $es);

            $intervals[] = compact('start', 'end');
        }

        $now = now();

        if ($count === 1) {
            $start = $intervals[0]['start'];
            $end   = $intervals[0]['end'];
            if ($now->lt($start)) return 'Ban hành';
            if ($now->between($start, $end)) return 'Đang đào tạo';
            return 'Kết thúc';
        }

        // ≥ 2 lịch: gộp giai đoạn lớn nhất
        $minStart = null;
        $maxEnd   = null;
        foreach ($intervals as $iv) {
            $minStart = $minStart ? $minStart->min($iv['start']) : $iv['start'];
            $maxEnd   = $maxEnd   ? $maxEnd->max($iv['end'])     : $iv['end'];
        }

        if ($now->lt($minStart)) return 'Ban hành';
        if ($now->between($minStart, $maxEnd)) return 'Đang đào tạo';
        return 'Kết thúc';
    }

    /** Đồng bộ cột trang_thai trong DB theo computeTrangThai() */
    public function syncTrangThai(): void
    {
        $new = $this->computeTrangThai();
        if ($this->trang_thai !== $new) {
            $this->forceFill(['trang_thai' => $new])->saveQuietly();
        }
    }
}
