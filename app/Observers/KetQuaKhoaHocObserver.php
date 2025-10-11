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
        if (is_null($kq->ket_qua)) {
            return;
        }

        if (!$kq->da_chuyen_duyet) {
            return;
        }

        $dk = $kq->dangKy()->first();
        if (!$dk) return;

        if ($kq->ket_qua === 'hoan_thanh') {
            DB::transaction(function () use ($kq, $dk) {
                HocVienKhongHoanThanh::where('ket_qua_khoa_hoc_id', $kq->id)->delete();
                HocVienHoanThanh::updateOrCreate(
                    ['ket_qua_khoa_hoc_id' => $kq->id],
                    [
                        'hoc_vien_id'      => $dk->hoc_vien_id,
                        'khoa_hoc_id'      => $dk->khoa_hoc_id,
                        'ngay_hoan_thanh'  => $kq->updated_at,
                        'ghi_chu'          => $kq->danh_gia_ren_luyen,
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
                        'ly_do_khong_hoan_thanh' => $kq->danh_gia_ren_luyen,
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
