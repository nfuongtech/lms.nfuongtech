<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class LichHoc extends Model
{
    use HasFactory;

    protected $table = 'lich_hocs';
    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (LichHoc $m) {
            if (! $m->ngay_hoc || ! $m->gio_bat_dau || ! $m->gio_ket_thuc) return;

            $start = strlen($m->gio_bat_dau) === 5
                ? Carbon::createFromFormat('H:i', $m->gio_bat_dau)
                : Carbon::createFromFormat('H:i:s', $m->gio_bat_dau);
            $end   = strlen($m->gio_ket_thuc) === 5
                ? Carbon::createFromFormat('H:i', $m->gio_ket_thuc)
                : Carbon::createFromFormat('H:i:s', $m->gio_ket_thuc);

            if ($end->lte($start)) {
                throw ValidationException::withMessages([
                    'gio_ket_thuc' => 'Giờ kết thúc phải sau giờ bắt đầu.',
                ]);
            }

            // Tự tính số giờ nếu không nhập (hoặc <= 0). Là SỐ NGUYÊN theo 60 phút = 1 giờ.
            if (!isset($m->so_gio_giang) || (int)$m->so_gio_giang <= 0) {
                $minutes = $end->diffInMinutes($start);
                $hours = max(1, (int) round($minutes / 60)); // làm tròn, tối thiểu 1
                $m->so_gio_giang = $hours;
            }

            // Tự tính tuần/tháng/năm
            $date = $m->ngay_hoc instanceof \DateTimeInterface ? Carbon::instance($m->ngay_hoc) : Carbon::parse($m->ngay_hoc);
            $m->tuan  = $m->tuan  ?: (int) $date->isoWeek();
            $m->thang = $m->thang ?: (int) $date->month;
            $m->nam   = $m->nam   ?: (int) $date->year;

            // Ghi tên phòng hiển thị từ dia_diem_id (nếu có)
            if ($m->dia_diem_id) {
                $m->dia_diem = optional($m->diaDiem)->ten_phong ?? $m->dia_diem;
            }

            // Kiểm tra trùng lịch chi tiết
            $dateStr = $date->toDateString();
            $startStr = $start->format('H:i:s');
            $endStr   = $end->format('H:i:s');

            $messages = [];

            // Trùng GIẢNG VIÊN + thời gian
            if ($m->giang_vien_id) {
                $conflictGV = static::query()
                    ->where('id', '!=', $m->id ?? 0)
                    ->whereDate('ngay_hoc', $dateStr)
                    ->where('giang_vien_id', $m->giang_vien_id)
                    ->where('gio_bat_dau', '<', $endStr)
                    ->where('gio_ket_thuc', '>', $startStr)
                    ->exists();
                if ($conflictGV) {
                    $messages['giang_vien_id'] = 'Lịch học trùng Giảng viên và Thời gian, vui lòng chọn lại.';
                }
            }

            // Trùng PHÒNG + thời gian (ưu tiên theo id; fallback theo tên phòng)
            $conflictPhong = false;
            if ($m->dia_diem_id) {
                $conflictPhong = static::query()
                    ->where('id', '!=', $m->id ?? 0)
                    ->whereDate('ngay_hoc', $dateStr)
                    ->where('dia_diem_id', $m->dia_diem_id)
                    ->where('gio_bat_dau', '<', $endStr)
                    ->where('gio_ket_thuc', '>', $startStr)
                    ->exists();
            } elseif (!empty($m->dia_diem)) {
                $conflictPhong = static::query()
                    ->where('id', '!=', $m->id ?? 0)
                    ->whereDate('ngay_hoc', $dateStr)
                    ->where('dia_diem', $m->dia_diem)
                    ->where('gio_bat_dau', '<', $endStr)
                    ->where('gio_ket_thuc', '>', $startStr)
                    ->exists();
            }

            if ($conflictPhong) {
                $messages['dia_diem_id'] = 'Lịch học trùng Thời gian và Phòng học, vui lòng chọn lại.';
            }

            if (!empty($messages)) {
                // Gắn thêm thông báo vào khung thời gian để user dễ thấy
                $messages['gio_bat_dau'] = $messages['gio_bat_dau'] ?? 'Vui lòng kiểm tra trùng giờ/giảng viên/phòng.';
                throw ValidationException::withMessages($messages);
            }
        });

        static::saved(function (LichHoc $m) { optional($m->khoaHoc)->syncTrangThai(); });
        static::deleted(function (LichHoc $m) { optional($m->khoaHoc)->syncTrangThai(); });
    }

    public function khoaHoc()   { return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id'); }
    public function chuyenDe()  { return $this->belongsTo(ChuyenDe::class, 'chuyen_de_id'); }
    public function giangVien() { return $this->belongsTo(GiangVien::class, 'giang_vien_id'); }
    public function diaDiem()   { return $this->belongsTo(DiaDiemDaoTao::class, 'dia_diem_id'); }
}
