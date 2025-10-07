<?php

namespace App\Observers;

use Illuminate\Support\Facades\DB;

class ScheduleConflictService
{
    /**
     * @return string[] Danh sách cảnh báo xung đột (phòng/GV)
     */
    public static function detect($ngay, $bat_dau, $ket_thuc, $khoa_hoc_id, $dia_diem_id, $giang_vien_id, $excludeId = null): array
    {
        if (!$ngay || !$bat_dau || !$ket_thuc) return [];

        $base = DB::table('lich_hocs')->whereDate('ngay_hoc', $ngay);
        if ($excludeId) $base->where('id', '!=', $excludeId);

        // overlap: (A.start < B.end) && (A.end > B.start)
        $overlap = function ($q) use ($bat_dau, $ket_thuc) {
            $q->where('gio_bat_dau', '<', $ket_thuc)->where('gio_ket_thuc', '>', $bat_dau);
        };

        $msgs = [];

        if ($dia_diem_id) {
            $room = (clone $base)->where('dia_diem_id', $dia_diem_id)->where($overlap)->exists();
            if ($room) $msgs[] = 'Trùng phòng học (ngày/giờ).';
        }

        if ($giang_vien_id) {
            $gv = (clone $base)->where('giang_vien_id', $giang_vien_id)->where($overlap)->exists();
            if ($gv) $msgs[] = 'Giảng viên đang bận khung giờ này.';
        }

        // Trùng học viên: sẽ bổ sung sau khi có bảng phân bổ học viên theo lớp
        return $msgs;
    }
}
