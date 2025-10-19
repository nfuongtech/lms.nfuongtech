<?php

namespace App\Observers;

use App\Models\DiemDanhBuoiHoc;
use App\Models\KetQuaKhoaHoc;

class DiemDanhBuoiHocObserver
{
    /**
     * Sau khi lưu điểm danh → tự động tính lại điểm & kết quả khóa học
     */
    public function saved(DiemDanhBuoiHoc $diemDanh): void
    {
        $dangKy = $diemDanh->dangKy;
        if (!$dangKy) {
            return;
        }

        $all = $dangKy->diemDanhs;

        $tongDiem = $all->whereNotNull('diem_buoi_hoc')->avg('diem_buoi_hoc');
        $soBuoi = $all->count();
        $soVang = $all->whereIn('trang_thai', ['vang_phep', 'vang_khong_phep'])->count();

        $tyLeVang = $soBuoi > 0 ? ($soVang / $soBuoi) * 100 : 0;

        $ketQua = ($tyLeVang <= 20 && $tongDiem >= 5) ? 'hoan_thanh' : 'khong_hoan_thanh';

        KetQuaKhoaHoc::updateOrCreate(
            ['dang_ky_id' => $dangKy->id],
            [
                'diem' => $tongDiem,
                'ket_qua' => $ketQua,
                'trang_thai_hoc_vien' => $ketQua,
            ]
        );
    }
}
