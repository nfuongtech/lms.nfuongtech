<?php

namespace App\Observers;

use App\Models\KetQuaKhoaHoc;
use App\Models\HocVienHoanThanh;
use App\Models\HocVienKhongHoanThanh;
use Illuminate\Support\Facades\DB;

class KetQuaKhoaHocObserver
{
    public function saved(KetQuaKhoaHoc $kq): void
    {
        // Chỉ xử lý khi ket_qua vừa đổi & có giá trị
        if (!$kq->wasChanged('ket_qua') || is_null($kq->ket_qua)) {
            return;
        }

        // Chỉ cho phép khi thao tác đến từ trang Cập nhật kết quả (trạm trung chuyển)
        if (!app()->runningInConsole()) {
            $path = request()?->path();
            if (!is_string($path) || !str_contains($path, 'admin/cap-nhat-ket-qua')) {
                return;
            }
        }

        $dk = $kq->dangKy()->first();
        if (!$dk) return;

        if ($kq->ket_qua === 'hoan_thanh') {
            DB::transaction(function () use ($kq, $dk) {
                HocVienKhongHoanThanh::where('ket_qua_khoa_hoc_id', $kq->id)->delete();
                HocVienHoanThanh::updateOrCreate(
                    ['ket_qua_khoa_hoc_id' => $kq->id],
                    [
                        'hoc_vien_id' => $dk->hoc_vien_id,
                        'khoa_hoc_id' => $dk->khoa_hoc_id,
                        'ghi_chu'     => $kq->ly_do_vang,
                    ],
                );
            });
        } elseif ($kq->ket_qua === 'khong_hoan_thanh') {
            DB::transaction(function () use ($kq, $dk) {
                HocVienHoanThanh::where('ket_qua_khoa_hoc_id', $kq->id)->delete();
                HocVienKhongHoanThanh::updateOrCreate(
                    ['ket_qua_khoa_hoc_id' => $kq->id],
                    [
                        'hoc_vien_id'            => $dk->hoc_vien_id,
                        'khoa_hoc_id'            => $dk->khoa_hoc_id,
                        'ly_do_khong_hoan_thanh' => $kq->ly_do_vang,
                    ],
                );
            });
        }
    }

    public function deleted(KetQuaKhoaHoc $kq): void
    {
        HocVienHoanThanh::where('ket_qua_khoa_hoc_id', $kq->id)->delete();
        HocVienKhongHoanThanh::where('ket_qua_khoa_hoc_id', $kq->id)->delete();
    }
}
