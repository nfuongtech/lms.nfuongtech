<?php

namespace App\Observers;

use App\Models\KetQuaKhoaHoc;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;

class KetQuaKhoaHocObserver
{
    /**
     * Khi ket_qua_khoa_hocs được lưu → tự động phân loại học viên
     * Mapping:
     * - 'hoan_thanh' tương đương 'Đạt'/'Hoàn thành'
     * - 'khong_hoan_thanh' tương đương 'Không đạt'/'Không hoàn thành'
     */
    public function saved(KetQuaKhoaHoc $ketQua): void
    {
        $hvId = $ketQua->dangKy->hoc_vien_id ?? null;
        $khId = $ketQua->dangKy->khoa_hoc_id ?? null;

        if (!$hvId || !$khId) {
            return;
        }

        if ($ketQua->ket_qua === 'hoan_thanh') {
            // GHI CHÚ chỉ áp dụng bảng 'hoc_vien_hoan_thanh' (bảng này có cột ghi_chu)
            HocVienHoanThanh::updateOrCreate(
                ['ket_qua_khoa_hoc_id' => $ketQua->id],
                [
                    'hoc_vien_id'      => $hvId,
                    'khoa_hoc_id'      => $khId,
                    'ngay_hoan_thanh'  => now(),
                    'chung_chi_da_cap' => false,
                    'ghi_chu'          => 'Tự động phân loại từ Observer',
                ]
            );

            // Loại bỏ bản ghi ở bảng "không hoàn thành" (nếu có) của cùng học viên & khóa
            HocVienKhongHoanThanh::where('hoc_vien_id', $hvId)
                ->where('khoa_hoc_id', $khId)
                ->delete();

        } elseif ($ketQua->ket_qua === 'khong_hoan_thanh') {
            // BẢNG 'hoc_vien_khong_hoan_thanh' KHÔNG có cột ghi_chu → không set 'ghi_chu'
            HocVienKhongHoanThanh::updateOrCreate(
                ['ket_qua_khoa_hoc_id' => $ketQua->id],
                [
                    'hoc_vien_id'             => $hvId,
                    'khoa_hoc_id'             => $khId,
                    'ly_do_khong_hoan_thanh'  => 'Không đạt yêu cầu',
                    'co_the_ghi_danh_lai'     => true, // đề xuất học lại khóa tương tự
                ]
            );

            // Loại bỏ bản ghi ở bảng "hoàn thành" (nếu có) của cùng học viên & khóa
            HocVienHoanThanh::where('hoc_vien_id', $hvId)
                ->where('khoa_hoc_id', $khId)
                ->delete();
        }
    }
}
