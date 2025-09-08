<?php

namespace App\Rules;

use App\Models\LichHoc;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class NoOverlappingSchedules implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $sessions = collect($value ?? [])
            ->filter(fn($s) => !empty($s['ngay_hoc']) && !empty($s['gio_bat_dau']) && !empty($s['gio_ket_thuc']))
            ->values();

        // Chuẩn hóa dữ liệu
        $normalized = $sessions->map(function ($s) use ($fail) {
            $date = Carbon::parse($s['ngay_hoc'])->toDateString();
            $start = Carbon::parse($date . ' ' . trim($s['gio_bat_dau']));
            $end   = Carbon::parse($date . ' ' . trim($s['gio_ket_thuc']));

            if ($end->lessThanOrEqualTo($start)) {
                $fail("Lỗi: Giờ kết thúc phải sau giờ bắt đầu (ngày {$date}).");
            }

            return [
                'id'            => $s['id'] ?? null, // khi edit sẽ có id
                'date'          => $date,
                'start'         => $start,
                'end'           => $end,
                'giang_vien_id' => $s['giang_vien_id'] ?? null,
                'dia_diem'      => $s['dia_diem'] ?? null,
            ];
        });

        // 1) Check overlap trong chính form nhập
        for ($i = 0; $i < $normalized->count(); $i++) {
            for ($j = $i + 1; $j < $normalized->count(); $j++) {
                $A = $normalized[$i];
                $B = $normalized[$j];

                if ($A['date'] !== $B['date']) {
                    continue;
                }

                $overlap = $A['start']->lt($B['end']) && $B['start']->lt($A['end']);

                if ($overlap) {
                    $fail("Lỗi: Có 2 buổi học bị chồng chéo trong kế hoạch ({$A['start']->format('d/m H:i')}–{$A['end']->format('H:i')} và {$B['start']->format('H:i')}–{$B['end']->format('H:i')}).");
                    return;
                }
            }
        }

        // 2) Check overlap với DB (bỏ qua chính record đang edit nếu có id)
        foreach ($normalized as $S) {
            // Giảng viên
            if (!empty($S['giang_vien_id'])) {
                $existingGV = LichHoc::query()
                    ->whereDate('ngay_hoc', $S['date'])
                    ->where('giang_vien_id', $S['giang_vien_id'])
                    ->when($S['id'], fn($q) => $q->where('id', '!=', $S['id'])) // bỏ qua chính nó
                    ->get();

                foreach ($existingGV as $ex) {
                    $date = Carbon::parse($ex->ngay_hoc)->toDateString();
                    $exStart = Carbon::parse($date . ' ' . trim($ex->gio_bat_dau));
                    $exEnd   = Carbon::parse($date . ' ' . trim($ex->gio_ket_thuc));

                    $overlap = $S['start']->lt($exEnd) && $exStart->lt($S['end']);
                    if ($overlap) {
                        $fail("Lỗi: Giảng viên đã có lịch {$exStart->format('d/m H:i')}–{$exEnd->format('H:i')}.");
                        return;
                    }
                }
            }

            // Phòng
            if (!empty($S['dia_diem'])) {
                $existingRoom = LichHoc::query()
                    ->whereDate('ngay_hoc', $S['date'])
                    ->where('dia_diem', $S['dia_diem'])
                    ->when($S['id'], fn($q) => $q->where('id', '!=', $S['id']))
                    ->get();

                foreach ($existingRoom as $ex) {
                    $date = Carbon::parse($ex->ngay_hoc)->toDateString();
                    $exStart = Carbon::parse($date . ' ' . trim($ex->gio_bat_dau));
                    $exEnd   = Carbon::parse($date . ' ' . trim($ex->gio_ket_thuc));

                    $overlap = $S['start']->lt($exEnd) && $exStart->lt($S['end']);
                    if ($overlap) {
                        $fail("Lỗi: Phòng '{$S['dia_diem']}' đã có lịch {$exStart->format('d/m H:i')}–{$exEnd->format('H:i')}.");
                        return;
                    }
                }
            }
        }
    }
}
