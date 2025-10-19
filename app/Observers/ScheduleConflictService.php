<?php

namespace App\Observers;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ScheduleConflictService
{
    /**
     * @return string[] Danh sách cảnh báo xung đột (phòng/GV)
     */
    public static function detect(
        $ngay,
        $bat_dau,
        $ket_thuc,
        $khoa_hoc_id,
        $dia_diem_id,
        $giang_vien_id,
        $excludeId = null,
        bool $skipForCurrentCourse = false,
        bool $overrideConflicts = false
    ): array
    {
        if (!$ngay || !$bat_dau || !$ket_thuc || $skipForCurrentCourse) {
            return [];
        }

        $base = DB::table('lich_hocs')
            ->whereDate('lich_hocs.ngay_hoc', $ngay)
            ->when($excludeId, fn ($q) => $q->where('lich_hocs.id', '!=', $excludeId))
            ->leftJoin('khoa_hocs', 'khoa_hocs.id', '=', 'lich_hocs.khoa_hoc_id')
            ->where(function ($query) {
                $query
                    ->whereNull('khoa_hocs.id')
                    ->orWhere(function ($sub) {
                        $sub->where(function ($inner) {
                            $inner->whereNull('khoa_hocs.tam_hoan')
                                ->orWhere('khoa_hocs.tam_hoan', false)
                                ->orWhere('khoa_hocs.tam_hoan', 0)
                                ->orWhere('khoa_hocs.tam_hoan', '0');
                        })->where(function ($inner) {
                            $inner->whereNull('khoa_hocs.trang_thai')
                                ->orWhereRaw('LOWER(khoa_hocs.trang_thai) NOT IN (?, ?, ?)', ['tam_hoan', 'tạm hoãn', 'tam hoan']);
                        });
                    });
            });

        // overlap: (A.start < B.end) && (A.end > B.start)
        $overlap = function ($q) use ($bat_dau, $ket_thuc) {
            $q->where('lich_hocs.gio_bat_dau', '<', $ket_thuc)->where('lich_hocs.gio_ket_thuc', '>', $bat_dau);
        };

        $msgs = [];

        if ($dia_diem_id) {
            $room = (clone $base)->where('lich_hocs.dia_diem_id', $dia_diem_id)->where($overlap)->exists();
            if ($room) $msgs[] = 'Trùng phòng học (ngày/giờ).';
        }

        if ($giang_vien_id) {
            $gv = (clone $base)->where('lich_hocs.giang_vien_id', $giang_vien_id)->where($overlap)->exists();
            if ($gv) $msgs[] = 'Giảng viên đang bận khung giờ này.';
        }

        if (!empty($msgs) && class_exists(Notification::class)) {
            Notification::make()
                ->title('Cảnh báo trùng lịch')
                ->body(implode("\n", $msgs))
                ->danger()
                ->persistent()
                ->send();
        }

        if ($overrideConflicts) {
            return [];
        }

        // Trùng học viên: sẽ bổ sung sau khi có bảng phân bổ học viên theo lớp
        return $msgs;
    }
}
