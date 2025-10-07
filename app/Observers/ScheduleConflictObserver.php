<?php

namespace App\Observers;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleConflictObserver
{
    public static function assertNoConflicts(int $khoaHocId, array $data, ?int $ignoreLichId = null): void
    {
        $ngay  = $data['ngay_hoc'] ?? null;
        $start = $data['gio_bat_dau'] ?? null;
        $end   = $data['gio_ket_thuc'] ?? null;
        $roomId= $data['dia_diem_id'] ?? null;
        $gvId  = $data['giang_vien_id'] ?? null;

        if (!$ngay || !$start || !$end) return;

        $overlapRaw = " (gio_bat_dau < :end) AND (gio_ket_thuc > :start) ";

        if ($roomId) {
            $roomClash = DB::table('lich_hocs')
                ->whereDate('ngay_hoc', $ngay)
                ->where('dia_diem_id', $roomId)
                ->whereRaw($overlapRaw, ['end' => $end, 'start' => $start])
                ->when($ignoreLichId, fn($q) => $q->where('id', '!=', $ignoreLichId))
                ->exists();
            if ($roomClash) throw ValidationException::withMessages(['dia_diem_id' => 'Trùng phòng học (đã có lịch trong khoảng giờ này).']);
        }

        if ($gvId) {
            $gvClash = DB::table('lich_hocs')
                ->whereDate('ngay_hoc', $ngay)
                ->where('giang_vien_id', $gvId)
                ->whereRaw($overlapRaw, ['end' => $end, 'start' => $start])
                ->when($ignoreLichId, fn($q) => $q->where('id', '!=', $ignoreLichId))
                ->exists();
            if ($gvClash) throw ValidationException::withMessages(['giang_vien_id' => 'Giảng viên đã có lịch trùng giờ.']);
        }

        // Trùng học viên giữa các khóa (nếu có bảng dang_kies)
        if (DB::getSchemaBuilder()->hasTable('dang_kies')) {
            $sql = "
                SELECT 1
                FROM dang_kies dk1
                JOIN dang_kies dk2 ON dk1.hoc_vien_id = dk2.hoc_vien_id
                JOIN lich_hocs lh2 ON lh2.khoa_hoc_id = dk2.khoa_hoc_id
                WHERE dk1.khoa_hoc_id = :kh
                  AND dk2.khoa_hoc_id <> :kh
                  AND DATE(lh2.ngay_hoc) = :ngay
                  AND (lh2.gio_bat_dau < :end) AND (lh2.gio_ket_thuc > :start)
                  ".($ignoreLichId ? " AND lh2.id <> :ignoreId " : "")."
                LIMIT 1
            ";

            $bindings = ['kh'=>$khoaHocId,'ngay'=>$ngay,'start'=>$start,'end'=>$end];
            if ($ignoreLichId) $bindings['ignoreId'] = $ignoreLichId;

            $hvClash = DB::selectOne($sql, $bindings);
            if ($hvClash) {
                throw ValidationException::withMessages(['ngay_hoc' => 'Có học viên của khóa này đang học trùng giờ ở khóa khác.']);
            }
        }
    }
}
