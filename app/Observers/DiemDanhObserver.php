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

        $rows = DiemDanh::where('dang_ky_id', $dkId)->get();
        $tong = 0; $count = 0;
        foreach ($rows as $r) {
            if (!is_null($r->diem_buoi_hoc)) { $tong += (float) $r->diem_buoi_hoc; $count++; }
        }
        $diem = $count > 0 ? round($tong / max(1, $count), 2) : null;

        $kq = KetQuaKhoaHoc::firstOrCreate(['dang_ky_id' => $dkId]);

        // Nếu đã chốt trước đó => mở lại để duyệt ở trạm trung chuyển
        if (!is_null($kq->ket_qua)) {
            $kq->ket_qua = null;
            HocVienHoanThanh::where('ket_qua_khoa_hoc_id', $kq->id)->delete();
            HocVienKhongHoanThanh::where('ket_qua_khoa_hoc_id', $kq->id)->delete();
        }

        $kq->diem_tong_khoa = $diem;
        $kq->needs_review   = true; // đưa vào danh sách chờ duyệt
        $kq->save();
    }
}
