<?php

namespace App\Observers;

use App\Models\DiemDanh;
use App\Models\KetQuaKhoaHoc;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;

class DiemDanhObserver
{
    public function saved(DiemDanh $dd): void
    {
        $dkId = (int) $dd->dang_ky_id;

        $dangKy = $dd->dangKy()->with('khoaHoc')->first();
        if ($dangKy && $dangKy->khoaHoc && $dangKy->khoaHoc->da_chuyen_ket_qua) {
            return;
        }

        $rows = DiemDanh::where('dang_ky_id', $dkId)->get();
        $tong = 0;
        $count = 0;
        foreach ($rows as $r) {
            if ($r->diem_buoi_hoc !== null) {
                $tong += (float) $r->diem_buoi_hoc;
                $count++;
            }
        }

        $diem = $count > 0 ? round($tong / $count, 2) : null;

        $ketQua = KetQuaKhoaHoc::firstOrCreate(['dang_ky_id' => $dkId]);
        if ($ketQua->ket_qua) {
            $ketQua->ket_qua = null;
            HocVienHoanThanh::where('ket_qua_khoa_hoc_id', $ketQua->id)->delete();
            HocVienKhongHoanThanh::where('ket_qua_khoa_hoc_id', $ketQua->id)->delete();
        }

        $ketQua->diem_trung_binh = $diem;
        $ketQua->needs_review = true;
        $ketQua->save();
    }
}
