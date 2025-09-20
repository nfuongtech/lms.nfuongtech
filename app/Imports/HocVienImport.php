<?php

namespace App\Imports;

use App\Models\HocVien;
use App\Models\DonVi;
use App\Models\DonViPhapNhan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class HocVienImport implements ToCollection, WithHeadingRow
{
    protected $report = [
        'inserted' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors'  => 0,
    ];

    protected $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                if (empty(trim((string)($row['ho_ten'] ?? '')))) {
                    $this->errors[] = ['row' => $index + 2, 'message' => 'Thiếu Họ tên'];
                    $this->report['errors']++;
                    continue;
                }

                // Sinh MSNV nếu không có
                $msnv = !empty($row['msnv']) ? trim($row['msnv']) : $this->generateMSNV();

                // Parse ngày
                $namSinh = $this->parseDate($row['nam_sinh'] ?? null);
                $ngayVao = $this->parseDate($row['ngay_vao'] ?? null);

                // --- Đơn vị pháp nhân ---
                $donViPhapNhan = null;
                $maSoThue = trim((string)($row['ma_so_thue'] ?? ''));
                $tenDonVi = trim((string)($row['ten_don_vi'] ?? ''));
                $diaChi = trim((string)($row['dia_chi'] ?? ''));

                if (!empty($maSoThue) || !empty($tenDonVi)) {
                    $key = $maSoThue ?: substr(md5($tenDonVi), 0, 12);

                    $donViPhapNhan = DonViPhapNhan::firstOrCreate(
                        ['ma_so_thue' => $key],
                        [
                            'ten_don_vi' => $tenDonVi ?: 'Chưa đặt tên',
                            'dia_chi'    => $diaChi,
                        ]
                    );

                    if ($tenDonVi && $donViPhapNhan->ten_don_vi !== $tenDonVi) {
                        $donViPhapNhan->ten_don_vi = $tenDonVi;
                        $donViPhapNhan->save();
                    }
                    if ($diaChi && $donViPhapNhan->dia_chi !== $diaChi) {
                        $donViPhapNhan->dia_chi = $diaChi;
                        $donViPhapNhan->save();
                    }
                }

                // --- Đơn vị ---
                $donVi = null;
                $maDonVi = trim((string)($row['ma_don_vi'] ?? ''));
                $tenDonViDV = trim((string)($row['ten_don_vi'] ?? ''));
                $thacoTdtv = trim((string)($row['thaco_tdtv'] ?? ''));
                $congTyBan = trim((string)($row['cong_ty_ban_nvqt'] ?? ''));
                $phongBoPhan = trim((string)($row['phong_bo_phan'] ?? ''));
                $noiLamViec = trim((string)($row['noi_lam_viec_chi_tiet'] ?? ''));

                if ($maDonVi) {
                    $donVi = DonVi::firstOrCreate(
                        ['ma_don_vi' => $maDonVi],
                        [
                            'ten_hien_thi' => $tenDonViDV ?: $maDonVi,
                            'thaco_tdtv' => $thacoTdtv,
                            'cong_ty_ban_nvqt' => $congTyBan,
                            'phong_bo_phan' => $phongBoPhan,
                            'noi_lam_viec_chi_tiet' => $noiLamViec,
                        ]
                    );
                } else {
                    $existingDonVi = DonVi::where('thaco_tdtv', $thacoTdtv)
                        ->where('cong_ty_ban_nvqt', $congTyBan)
                        ->where('phong_bo_phan', $phongBoPhan)
                        ->first();

                    if ($existingDonVi) {
                        $donVi = $existingDonVi;
                    } else {
                        $baseCode = Str::upper(Str::slug(($thacoTdtv . '_' . $congTyBan . '_' . $phongBoPhan), '_'));
                        if ($baseCode === '') {
                            $baseCode = 'DV_' . now()->format('ymdHis');
                        }
                        $code = $baseCode;
                        $i = 1;
                        while (DonVi::where('ma_don_vi', $code)->exists()) {
                            $code = $baseCode . '_' . $i++;
                        }

                        $donVi = DonVi::create([
                            'ma_don_vi' => $code,
                            'ten_hien_thi' => $tenDonViDV ?: $phongBoPhan ?: $congTyBan ?: $thacoTdtv ?: $code,
                            'thaco_tdtv' => $thacoTdtv,
                            'cong_ty_ban_nvqt' => $congTyBan,
                            'phong_bo_phan' => $phongBoPhan,
                            'noi_lam_viec_chi_tiet' => $noiLamViec,
                        ]);
                    }
                }

                // --- Số điện thoại ---
                $sdt = null;
                if (isset($row['sdt'])) {
                    $rawSdt = trim((string)$row['sdt']);

                    // Nếu là số kiểu Excel (vd: 986377988)
                    if (is_numeric($row['sdt'])) {
                        $sdt = (string)$row['sdt'];
                        if ($sdt && $sdt[0] !== '0') {
                            $sdt = '0' . $sdt;
                        }
                    }
                    // Nếu bắt đầu bằng +84 -> chuyển thành 0...
                    elseif (str_starts_with($rawSdt, '+84')) {
                        $sdt = '0' . substr($rawSdt, 3);
                    }
                    // Các trường hợp khác -> giữ nguyên text
                    else {
                        $sdt = $rawSdt;
                    }
                }

                // --- Học viên ---
                $hocVien = HocVien::where('msnv', $msnv)->first();
                $dataUpdate = [
                    'ho_ten' => $row['ho_ten'],
                    'gioi_tinh' => $row['gioi_tinh'] ?? null,
                    'nam_sinh' => $namSinh,
                    'ngay_vao' => $ngayVao,
                    'chuc_vu' => $row['chuc_vu'] ?? null,
                    'email'   => $row['email'] ?? null,
                    'sdt'     => $sdt,
                    'tinh_trang' => $row['tinh_trang'] ?? 'Đang làm việc',
                    'don_vi_id' => $donVi?->id,
                    'don_vi_phap_nhan_id' => $donViPhapNhan?->ma_so_thue,
                ];

                if ($hocVien) {
                    $hocVien->update($dataUpdate);
                    $this->report['updated']++;
                } else {
                    $dataUpdate['msnv'] = $msnv;
                    HocVien::create($dataUpdate);
                    $this->report['inserted']++;
                }
            } catch (\Throwable $e) {
                $this->errors[] = ['row' => $index + 2, 'message' => $e->getMessage()];
                $this->report['errors']++;
                Log::error('Import HocVien error', ['row' => $index + 2, 'error' => $e->getMessage()]);
            }
        }
    }

    protected function parseDate($value)
    {
        if ($value === null || $value === '') return null;

        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float)$value)->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        $str = trim((string)$value);

        try {
            return Carbon::createFromFormat('d/m/Y', $str)->format('Y-m-d');
        } catch (\Throwable $e) {}

        $formats = ['Y-m-d', 'd-m-Y', 'Y/m/d', 'm/d/Y', 'd.m.Y'];
        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $str)->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function generateMSNV()
    {
        $prefix = 'HV-' . Carbon::now()->format('ym');
        $latest = HocVien::where('msnv', 'like', $prefix . '%')->orderBy('msnv', 'desc')->first();
        $num = $latest && preg_match('/(\d{3})$/', $latest->msnv, $m) ? intval($m[1]) + 1 : 1;
        return $prefix . str_pad(min($num, 999), 3, '0', STR_PAD_LEFT);
    }

    public function getReport() { return $this->report; }
    public function getErrors() { return $this->errors; }

    public function report()
    {
        Storage::makeDirectory('import_reports');
        $fileName = 'import_reports/hocvien_import_errors_' . now()->format('Ymd_His') . '.csv';
        $handle = fopen(storage_path('app/' . $fileName), 'w');
        fputcsv($handle, ['Dòng', 'Lỗi']);
        foreach ($this->errors as $error) {
            fputcsv($handle, [$error['row'] ?? '', $error['message'] ?? '']);
        }
        fclose($handle);
        return $fileName;
    }
}
