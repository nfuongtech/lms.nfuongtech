<?php

namespace App\Observers;

use App\Models\DangKy;
use App\Models\KetQuaKhoaHoc;

class DangKyObserver
{
    /**
     * Khi tạo mới một đăng ký → tự động tạo bản ghi ket_qua_khoa_hocs rỗng
     */
    public function created(DangKy $dangKy): void
    {
        KetQuaKhoaHoc::firstOrCreate(
            ['dang_ky_id' => $dangKy->id],
            [
                'diem' => null,
                'ket_qua' => null,
                'trang_thai_hoc_vien' => null,
            ]
        );
    }
}
