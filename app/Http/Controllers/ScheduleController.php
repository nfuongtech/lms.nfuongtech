<?php

namespace App\Http\Controllers;

use App\Models\LichHoc;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function getEvents()
    {
        $events = LichHoc::with(['khoaHoc.chuyenDe', 'giangVien'])->get()->map(function ($lichHoc) {
            return [
                'title' => $lichHoc->khoaHoc->chuyenDe->ten_chuyen_de . ' - ' . $lichHoc->khoaHoc->ten_khoa_hoc,
                'start' => $lichHoc->ngay_hoc . 'T' . $lichHoc->gio_bat_dau,
                'end' => $lichHoc->ngay_hoc . 'T' . $lichHoc->gio_ket_thuc,
                'extendedProps' => [
                    'giangVien' => $lichHoc->giangVien->ho_ten ?? 'N/A',
                    'diaDiem' => $lichHoc->dia_diem ?? 'N/A',
                ]
            ];
        });

        return response()->json($events);
    }
}
