<?php

namespace App\Observers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleConflictService
{
    /**
     * Kiểm tra trùng lịch (giảng viên/phòng) theo ngày + khoảng giờ giao nhau.
     * Bỏ qua lịch thuộc khoá học đang tạm hoãn.
     *
     * @param  array{ngay_hoc?:string,gio_bat_dau?:string,gio_ket_thuc?:string,giang_vien_id?:int,dia_diem_id?:int,khoa_hoc_id?:int,ignore_id?:int} $data
     * @return \Illuminate\Support\Collection<int,array<string,mixed>>
     */
    public function detectConflicts(array $data): Collection
    {
        $ngay  = $data['ngay_hoc']     ?? null;
        $start = $data['gio_bat_dau']  ?? null;
        $end   = $data['gio_ket_thuc'] ?? null;

        if (!$ngay || !$start || !$end) {
            return collect();
        }

        $ignoreId = $data['ignore_id'] ?? null;

        $q = DB::table('lich_hocs as lh')
            ->join('khoa_hocs as kh', 'kh.id', '=', 'lh.khoa_hoc_id')
            ->selectRaw('lh.id, lh.khoa_hoc_id, kh.ma_khoa_hoc, kh.ten_khoa_hoc, lh.ngay_hoc, lh.gio_bat_dau, lh.gio_ket_thuc, lh.giang_vien_id, lh.dia_diem_id')
            ->whereDate('lh.ngay_hoc', $ngay)
            ->where('kh.tam_hoan', 0);

        if ($ignoreId) {
            $q->where('lh.id', '!=', $ignoreId);
        }

        // khoảng giờ giao nhau
        $q->whereRaw('? < TIME_FORMAT(lh.gio_ket_thuc, "%H:%i:%s") AND ? > TIME_FORMAT(lh.gio_bat_dau, "%H:%i:%s")', [
            $start, $end,
        ]);

        $rows = $q->get();

        $conflicts = collect();

        foreach ($rows as $r) {
            if (!empty($data['giang_vien_id']) && $r->giang_vien_id && (int)$r->giang_vien_id === (int)$data['giang_vien_id']) {
                $conflicts->push([
                    'type' => 'giang_vien',
                    'id'   => (int)$r->giang_vien_id,
                    'khoa_hoc_id' => (int)$r->khoa_hoc_id,
                    'ma_khoa_hoc' => $r->ma_khoa_hoc,
                    'ten_khoa_hoc'=> $r->ten_khoa_hoc,
                    'ngay' => date('d/m/Y', strtotime($r->ngay_hoc)),
                    'gio'  => substr($r->gio_bat_dau,0,5).'-'.substr($r->gio_ket_thuc,0,5),
                ]);
            }
            if (!empty($data['dia_diem_id']) && $r->dia_diem_id && (int)$r->dia_diem_id === (int)$data['dia_diem_id']) {
                $conflicts->push([
                    'type' => 'dia_diem',
                    'id'   => (int)$r->dia_diem_id,
                    'khoa_hoc_id' => (int)$r->khoa_hoc_id,
                    'ma_khoa_hoc' => $r->ma_khoa_hoc,
                    'ten_khoa_hoc'=> $r->ten_khoa_hoc,
                    'ngay' => date('d/m/Y', strtotime($r->ngay_hoc)),
                    'gio'  => substr($r->gio_bat_dau,0,5).'-'.substr($r->gio_ket_thuc,0,5),
                ]);
            }
        }

        return $conflicts;
    }
}
