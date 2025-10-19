<?php

namespace App\Exports;

use App\Models\KhoaHoc;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DanhGiaKhoaHocExport implements FromArray, WithHeadings
{
    protected KhoaHoc $khoaHoc;

    protected array $lichHocs;

    protected array $requirements;

    protected int $soLuongHocVien;

    public function __construct(KhoaHoc $khoaHoc, array $lichHocs, array $requirements, int $soLuongHocVien)
    {
        $this->khoaHoc = $khoaHoc;
        $this->lichHocs = $lichHocs;
        $this->requirements = $requirements;
        $this->soLuongHocVien = $soLuongHocVien;
    }

    public function headings(): array
    {
        return [
            'Mã khóa',
            'Tên khóa học',
            'Ngày học',
            'Khung giờ',
            'Địa điểm',
            'Giảng viên',
            'Yêu cầu % giờ học',
            'Yêu cầu điểm TB',
            'Tổng giờ kế hoạch',
            'Số lượng học viên',
        ];
    }

    public function array(): array
    {
        $rows = [];
        $maKhoa = $this->khoaHoc->ma_khoa_hoc ?? '';
        $tenKhoa = $this->khoaHoc->chuongTrinh->ten_chuong_trinh ?? $this->khoaHoc->ten_khoa_hoc ?? '';
        $yeuCauGio = $this->requirements['yeu_cau_gio'] ?? null;
        $yeuCauDiem = $this->requirements['yeu_cau_diem'] ?? null;
        $tongGio = $this->requirements['tong_gio_ke_hoach'] ?? null;

        if (empty($this->lichHocs)) {
            $rows[] = [
                $maKhoa,
                $tenKhoa,
                '',
                '',
                '',
                '',
                $this->formatDecimal($yeuCauGio, ''),
                $this->formatDecimal($yeuCauDiem, ''),
                $this->formatDecimal($tongGio, ''),
                $this->soLuongHocVien,
            ];

            return $rows;
        }

        foreach ($this->lichHocs as $lichHoc) {
            $rows[] = [
                $maKhoa,
                $tenKhoa,
                $lichHoc['mo_ta'] ?? '',
                $this->extractThoiGian($lichHoc['mo_ta'] ?? ''),
                $this->extractDiaDiem($lichHoc['mo_ta'] ?? ''),
                $this->tenGiangVien($lichHoc['giang_vien'] ?? null),
                $this->formatDecimal($yeuCauGio, ''),
                $this->formatDecimal($yeuCauDiem, ''),
                $this->formatDecimal($tongGio, ''),
                $this->soLuongHocVien,
            ];
        }

        return $rows;
    }

    protected function formatDecimal($value, string $fallback): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        $formatted = number_format((float) $value, 1, '.', '');
        $trimmed = rtrim(rtrim($formatted, '0'), '.');

        return $trimmed === '' ? '0' : $trimmed;
    }

    protected function extractThoiGian(?string $description): string
    {
        if (!$description) {
            return '';
        }

        if (preg_match('/(\d{1,2}:\d{2}\s*-\s*\d{1,2}:\d{2})/u', $description, $matches)) {
            return $matches[1];
        }

        return '';
    }

    protected function extractDiaDiem(?string $description): string
    {
        if (!$description) {
            return '';
        }

        $parts = explode('·', $description);
        return trim(end($parts));
    }

    protected function tenGiangVien($giangVien): string
    {
        if (is_object($giangVien) && isset($giangVien->ho_ten)) {
            return $giangVien->ho_ten;
        }

        return is_string($giangVien) ? $giangVien : '';
    }
}
