<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class LichHoc extends Model
{
    use HasFactory;

    protected $table = 'lich_hocs';

    protected $fillable = [
        'khoa_hoc_id',
        'chuyen_de_id',
        'giang_vien_id',
        'dia_diem_id',
        'ngay_hoc',
        'gio_bat_dau',
        'gio_ket_thuc',
        'so_bai_kiem_tra',
        'so_gio_giang',
        'tuan',
        'thang',
        'nam',
    ];

    protected $casts = [
        'ngay_hoc' => 'date:Y-m-d',
    ];

    public function khoaHoc()   { return $this->belongsTo(KhoaHoc::class); }
    public function chuyenDe()  { return $this->belongsTo(ChuyenDe::class, 'chuyen_de_id'); }
    public function giangVien() { return $this->belongsTo(GiangVien::class, 'giang_vien_id'); }
    public function diaDiem()   { return $this->belongsTo(DiaDiemDaoTao::class, 'dia_diem_id'); }

    protected static function booted(): void
    {
        static::saving(function (LichHoc $m) {
            // Chuẩn hóa giờ 24h (không giây)
            $start = self::parseTime24($m->gio_bat_dau);
            $end   = self::parseTime24($m->gio_ket_thuc);

            if ($start && $end) {
                if ($end->lessThanOrEqualTo($start)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'gio_ket_thuc' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu (định dạng 24h H:i).',
                    ]);
                }
                // Auto tính nếu để trống (nhưng vẫn cho nhập tay)
                if ($m->so_gio_giang === null || $m->so_gio_giang === '') {
                    $m->so_gio_giang = max(0, (int) floor($start->diffInMinutes($end) / 60));
                }
                $m->gio_bat_dau  = $start->format('H:i:s');
                $m->gio_ket_thuc = $end->format('H:i:s');
            }

            // Tính tuần/tháng/năm ISO
            if ($m->ngay_hoc) {
                $d = Carbon::parse($m->ngay_hoc);
                $m->tuan  = (int) $d->isoWeek();
                $m->thang = (int) $d->month;
                $m->nam   = (int) $d->year;
            }

            // Cảnh báo xung đột phòng/GV
            $conflicts = \App\Observers\ScheduleConflictService::detect(
                $m->ngay_hoc,
                $start ? $start->format('H:i:s') : null,
                $end ? $end->format('H:i:s') : null,
                $m->khoa_hoc_id,
                $m->dia_diem_id,
                $m->giang_vien_id,
                $m->getKey()
            );
            if (!empty($conflicts)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'ngay_hoc' => implode("\n", $conflicts),
                ]);
            }
        });
    }

    public static function parseTime24($value): ?Carbon
    {
        if (!$value) return null;
        $v = preg_replace('/[^0-9:]/', '', trim((string) $value));
        try {
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $v)) return Carbon::createFromFormat('H:i:s', $v);
            if (preg_match('/^\d{2}:\d{2}$/', $v))    return Carbon::createFromFormat('H:i', $v);
            return Carbon::parse($v);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
