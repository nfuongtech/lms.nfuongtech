<?php

namespace App\Imports;

use App\Models\DiemDanh;
use App\Models\DangKy;
use App\Models\LichHoc;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class DiemDanhImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Validate dữ liệu đầu vào
        $validator = Validator::make($row, [
            'msnv' => 'required|string|max:50',
            'ngay_hoc' => 'required|date_format:d/m/Y',
            'gio_bat_dau' => 'required|regex:/^([01]\d|2[0-3]):([0-5]\d)$/',
            'gio_ket_thuc' => 'required|regex:/^([01]\d|2[0-3]):([0-5]\d)$/',
            'trang_thai' => 'required|in:co_mat,vang_phep,vang_khong_phep',
            'ly_do_vang' => 'nullable|string|max:255',
            'diem_buoi_hoc' => 'nullable|numeric|min:0|max:10',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Tìm học viên theo MSNV
        $hocVien = \App\Models\HocVien::where('msnv', $row['msnv'])->first();
        if (!$hocVien) {
            throw new ValidationException("Không tìm thấy học viên với MSNV: {$row['msnv']}");
        }

        // Tìm khóa học theo ngày học, giờ bắt đầu, giờ kết thúc
        $ngayHoc = Carbon::createFromFormat('d/m/Y', $row['ngay_hoc'])->format('Y-m-d');
        $gioBatDau = $row['gio_bat_dau'];
        $gioKetThuc = $row['gio_ket_thuc'];

        $lichHoc = LichHoc::where('ngay_hoc', $ngayHoc)
            ->where('gio_bat_dau', $gioBatDau)
            ->where('gio_ket_thuc', $gioKetThuc)
            ->first();

        if (!$lichHoc) {
            throw new ValidationException("Không tìm thấy buổi học vào ngày {$row['ngay_hoc']} từ {$gioBatDau} đến {$gioKetThuc}");
        }

        // Tìm đăng ký học viên cho khóa học này
        $dangKy = DangKy::where('hoc_vien_id', $hocVien->id)
            ->where('khoa_hoc_id', $lichHoc->khoa_hoc_id)
            ->first();

        if (!$dangKy) {
            throw new ValidationException("Học viên {$row['msnv']} chưa được ghi danh vào khóa học {$lichHoc->khoaHoc->ma_khoa_hoc}");
        }

        // Tạo hoặc cập nhật điểm danh
        return DiemDanh::updateOrCreate(
            [
                'dang_ky_id' => $dangKy->id,
                'lich_hoc_id' => $lichHoc->id,
            ],
            [
                'trang_thai' => $row['trang_thai'],
                'ly_do_vang' => $row['ly_do_vang'] ?? null,
                'diem_buoi_hoc' => $row['diem_buoi_hoc'] ?? null,
            ]
        );
    }
}
