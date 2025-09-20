<?php

namespace App\Observers;

use App\Models\KetQuaKhoaHoc;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;

class KetQuaKhoaHocObserver
{
    /**
     * Khi ket_qua_khoa_hocs được lưu → tự động phân loại học viên
     */
    public function saved(KetQuaKhoaHoc $ketQua): void
    {
        $hvId = $ketQua->dangKy->hoc_vien_id ?? null;
        if (!$hvId) {
            return;
        }

        if ($ketQua->ket_qua === 'hoan_thanh') {
            HocVienHoanThanh::updateOrCreate(
                ['ket_qua_khoa_hoc_id' => $ketQua->id],
                ['hoc_vien_id' => $hvId]
            );
            HocVienKhongHoanThanh::where('hoc_vien_id', $hvId)->delete();
        } elseif ($ketQua->ket_qua === 'khong_hoan_thanh') {
            HocVienKhongHoanThanh::updateOrCreate(
                ['ket_qua_khoa_hoc_id' => $ketQua->id],
                ['hoc_vien_id' => $hvId]
            );
            HocVienHoanThanh::where('hoc_vien_id', $hvId)->delete();
        }
    }
}
