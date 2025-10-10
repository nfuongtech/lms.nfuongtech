<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class LichHoc extends Model
{
    use HasFactory;

    protected $table = 'lich_hocs';
    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (LichHoc $m) {
            $force = (bool) (
                $m->getAttribute('force_override') ??
                $m->getAttribute('_force_override') ??
                false
            );
            if ($m->getAttribute('force_override') !== null) $m->offsetUnset('force_override');
            if ($m->getAttribute('_force_override') !== null) $m->offsetUnset('_force_override');

            $kh = $m->khoaHoc;
            if ($kh && ($kh->tam_hoan || $kh->computeTrangThai() === 'Kết thúc')) {
                throw ValidationException::withMessages([
                    'ngay_hoc'      => 'Kế hoạch đang ở trạng thái không cho phép lập/sửa lịch.',
                    'giang_vien_id' => 'Kế hoạch đang ở trạng thái không cho phép lập/sửa lịch.',
                    'dia_diem_id'   => 'Kế hoạch đang ở trạng thái không cho phép lập/sửa lịch.',
                ]);
            }

            if (! $m->ngay_hoc || ! $m->gio_bat_dau || ! $m->gio_ket_thuc) {
                return;
            }

            $gbd = $m->gio_bat_dau instanceof \DateTimeInterface
                ? $m->gio_bat_dau->format('H:i:s')
                : (strlen((string) $m->gio_bat_dau) === 5 ? $m->gio_bat_dau . ':00' : (string) $m->gio_bat_dau);

            $gkt = $m->gio_ket_thuc instanceof \DateTimeInterface
                ? $m->gio_ket_thuc->format('H:i:s')
                : (strlen((string) $m->gio_ket_thuc) === 5 ? $m->gio_ket_thuc . ':00' : (string) $m->gio_ket_thuc);

            $start = Carbon::createFromFormat('H:i:s', $gbd);
            $end   = Carbon::createFromFormat('H:i:s', $gkt);

            if ($end->lte($start)) {
                throw ValidationException::withMessages([
                    'gio_ket_thuc' => 'Giờ kết thúc phải sau giờ bắt đầu.',
                ]);
            }

            $minutes = $end->diffInMinutes($start);
            if ($minutes > 8 * 60) {
                throw ValidationException::withMessages([
                    'gio_ket_thuc' => 'Thời gian đào tạo liên tục không quá 8 tiếng.',
                ]);
            }

            // ✅ Cho phép số lẻ > 0
            $rawHours = $m->getAttribute('so_gio_giang');
            $hours = is_numeric($rawHours) ? (float) $rawHours : 0.0;
            if ($hours <= 0) {
                throw ValidationException::withMessages([
                    'so_gio_giang' => 'Vui lòng nhập Số giờ giảng > 0 (có thể nhập số lẻ).',
                ]);
            }

            $date = $m->ngay_hoc instanceof \DateTimeInterface
                ? Carbon::instance($m->ngay_hoc)
                : Carbon::parse($m->ngay_hoc);

            $m->tuan  = $m->tuan  ?: (int) $date->isoWeek();
            $m->thang = $m->thang ?: (int) $date->month;
            $m->nam   = $m->nam   ?: (int) $date->year;

            if ($m->dia_diem_id) {
                $m->dia_diem = optional($m->diaDiem)->ten_phong ?? $m->dia_diem;
            }

            if (! $force) {
                $errors = [];

                $base = static::query()
                    ->where('id', '!=', $m->id ?? 0)
                    ->whereDate('ngay_hoc', $date->toDateString())
                    ->where('gio_bat_dau', '<', $gkt)
                    ->where('gio_ket_thuc', '>', $gbd)
                    ->whereHas('khoaHoc', function ($q) {
                        $q->where('tam_hoan', false)
                          ->where('trang_thai', '!=', 'Kết thúc');
                    });

                if ($m->giang_vien_id) {
                    if ((clone $base)->where('giang_vien_id', $m->giang_vien_id)->exists()) {
                        $errors['giang_vien_id'] = 'Lịch học trùng giảng viên và thời gian.';
                        $errors['gio_bat_dau']   = $errors['gio_bat_dau']   ?? 'Thời gian bị trùng với lịch khác của giảng viên.';
                        $errors['gio_ket_thuc']  = $errors['gio_ket_thuc']  ?? 'Thời gian bị trùng với lịch khác của giảng viên.';
                    }
                }

                if ($m->dia_diem_id || $m->dia_diem) {
                    $conflictRoom = (clone $base)->where(function ($q) use ($m) {
                        if ($m->dia_diem_id) $q->orWhere('dia_diem_id', $m->dia_diem_id);
                        if ($m->dia_diem)    $q->orWhere('dia_diem', $m->dia_diem);
                    })->exists();
                    if ($conflictRoom) {
                        $errors['dia_diem_id']  = 'Lịch học trùng phòng học và thời gian.';
                        $errors['gio_bat_dau']  = $errors['gio_bat_dau']  ?? 'Thời gian bị trùng với lịch khác trong phòng.';
                        $errors['gio_ket_thuc'] = $errors['gio_ket_thuc'] ?? 'Thời gian bị trùng với lịch khác trong phòng.';
                    }
                }

                if (!empty($errors)) {
                    throw ValidationException::withMessages($errors);
                }
            }
        });

        static::saved(function (LichHoc $m) {
            optional($m->khoaHoc)->syncTrangThai();
        });
        static::deleted(function (LichHoc $m) {
            optional($m->khoaHoc)->syncTrangThai();
        });
    }

    public function khoaHoc()   { return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id'); }
    public function chuyenDe()  { return $this->belongsTo(ChuyenDe::class, 'chuyen_de_id'); }
    public function giangVien() { return $this->belongsTo(GiangVien::class, 'giang_vien_id'); }
    public function diaDiem()   { return $this->belongsTo(DiaDiemDaoTao::class, 'dia_diem_id'); }
}
