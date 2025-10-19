<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
                    $m->so_gio_giang = round($start->diffInMinutes($end) / 60, 2);
                }
                $m->gio_bat_dau  = $start->format('H:i:s');
                $m->gio_ket_thuc = $end->format('H:i:s');
            }

            if ($m->so_gio_giang !== null && $m->so_gio_giang !== '') {
                $m->so_gio_giang = round((float) str_replace(',', '.', (string) $m->so_gio_giang), 2);
            }

            // Tính tuần/tháng/năm ISO
            if ($m->ngay_hoc) {
                $d = Carbon::parse($m->ngay_hoc);
                $m->tuan  = (int) $d->isoWeek();
                $m->thang = (int) $d->month;
                $m->nam   = (int) $d->year;
            }

            // Cảnh báo xung đột phòng/GV
            $override = false;
            if (function_exists('request')) {
                $request = request();
                if ($request) {
                    foreach (Arr::dot($request->all()) as $key => $value) {
                        if (!Str::endsWith((string) $key, 'bo_qua_trung_lich')) {
                            continue;
                        }

                        $flag = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if ($flag === null) {
                            $flag = in_array($value, ['1', 1, 'on', 'true'], true);
                        }

                        if ($flag) {
                            $override = true;
                            break;
                        }
                    }
                }
            }

            $course = $m->relationLoaded('khoaHoc') ? $m->khoaHoc : $m->khoaHoc()->first();
            $skipForCurrentCourse = false;
            if ($course) {
                $statusSlug  = Str::slug((string) ($course->trang_thai ?? ''));
                $displaySlug = Str::slug((string) ($course->trang_thai_hien_thi ?? ''));
                $skipForCurrentCourse = ($course->tam_hoan ?? false)
                    || $statusSlug === 'tam-hoan'
                    || $displaySlug === 'tam-hoan';
            }

            $conflicts = \App\Observers\ScheduleConflictService::detect(
                $m->ngay_hoc,
                $start ? $start->format('H:i:s') : null,
                $end ? $end->format('H:i:s') : null,
                $m->khoa_hoc_id,
                $m->dia_diem_id,
                $m->giang_vien_id,
                $m->getKey(),
                $skipForCurrentCourse,
                $override
            );
            if (!empty($conflicts)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'ngay_hoc' => implode("\n", $conflicts) . "\nChọn \"Ghi đè lịch trùng\" để bỏ qua kiểm tra khi cần.",
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
