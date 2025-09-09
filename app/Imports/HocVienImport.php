<?php

namespace App\Imports;

use App\Models\HocVien;
use App\Models\DonVi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;

class HocVienImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, ShouldQueue
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Tìm hoặc tạo đơn vị
        $donVi = DonVi::firstOrCreate(
            ['ma_don_vi' => $row['ma_don_vi']], // Giả định có cột ma_don_vi trong file Excel
            [
                'ten_hien_thi' => $row['ten_don_vi'] ?? 'N/A',
                'thaco_tdtv' => $row['thaco_tdtv'] ?? 'N/A',
                'cong_ty_ban_nvqt' => $row['cong_ty_ban_nvqt'] ?? 'N/A',
                'phong_bo_phan' => $row['phong_bo_phan'] ?? null,
                'noi_lam_viec_chi_tiet' => $row['noi_lam_viec_chi_tiet'] ?? null,
            ]
        );

        return new HocVien([
            'msnv' => $row['msnv'] ?? null,
            'ho_ten' => $row['ho_ten'],
            'gioi_tinh' => $row['gioi_tinh'] ?? null,
            'nam_sinh' => $row['nam_sinh'] ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['nam_sinh'])->format('Y-m-d') : null,
            'email' => $row['email'] ?? null,
            'ngay_vao' => $row['ngay_vao'] ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['ngay_vao'])->format('Y-m-d') : null,
            'chuc_vu' => $row['chuc_vu'] ?? null,
            'don_vi_id' => $donVi->id,
            'tinh_trang' => $row['tinh_trang'] ?? 'Đang làm việc', // Mặc định
            'hinh_anh_path' => $row['hinh_anh_path'] ?? null,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
