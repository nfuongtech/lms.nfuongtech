<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DanhSachHocVienDanhGiaExport implements FromArray, WithHeadings
{
    protected array $hocVienRows;

    protected array $diemDanhData;

    protected array $tongKetData;

    protected array $visibleSessions;

    protected bool $showDtb;

    protected bool $showKetQua;

    protected bool $showDanhGia;

    protected bool $showHanhDong;

    public function __construct(
        array $hocVienRows,
        array $diemDanhData,
        array $tongKetData,
        array $visibleSessions,
        bool $showDtb,
        bool $showKetQua,
        bool $showDanhGia,
        bool $showHanhDong
    ) {
        $this->hocVienRows = $hocVienRows;
        $this->diemDanhData = $diemDanhData;
        $this->tongKetData = $tongKetData;
        $this->visibleSessions = $visibleSessions;
        $this->showDtb = $showDtb;
        $this->showKetQua = $showKetQua;
        $this->showDanhGia = $showDanhGia;
        $this->showHanhDong = $showHanhDong;
    }

    public function headings(): array
    {
        $headings = ['TT', 'Mã số', 'Họ & Tên'];

        foreach ($this->visibleSessions as $session) {
            $headings[] = $session['label'];
        }

        if ($this->showDtb) {
            $headings[] = 'ĐTB';
        }

        if ($this->showKetQua) {
            $headings[] = 'Kết quả';
        }

        if ($this->showDanhGia) {
            $headings[] = 'Đánh giá rèn luyện';
        }

        if ($this->showHanhDong) {
            $headings[] = 'Ghi chú hành động';
        }

        return $headings;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->hocVienRows as $index => $row) {
            $dangKyId = $row['dang_ky_id'];
            $hocVien = $row['hoc_vien'];
            $tongKet = $this->tongKetData[$dangKyId] ?? [];

            $data = [
                $index + 1,
                $hocVien->msnv ?? '',
                $hocVien->ho_ten ?? '',
            ];

            foreach ($this->visibleSessions as $session) {
                $lichHocId = $session['id'];
                $cell = $this->diemDanhData[$dangKyId][$lichHocId] ?? [];
                $status = $cell['trang_thai'] ?? 'co_mat';
                $statusLabel = ['co_mat' => 'Có mặt', 'vang_phep' => 'Vắng P', 'vang_khong_phep' => 'Vắng KP'][$status] ?? 'Có mặt';

                if ($status === 'co_mat') {
                    $gio = $this->formatDecimal($cell['so_gio_hoc'] ?? null, '-');
                    $diem = $this->formatDecimal($cell['diem'] ?? null, '-');
                    $data[] = sprintf('%s | Giờ: %s | Điểm: %s', $statusLabel, $gio, $diem);
                } else {
                    $lyDo = trim((string) ($cell['ly_do_vang'] ?? ''));
                    $data[] = $lyDo !== '' ? sprintf('%s | %s', $statusLabel, $lyDo) : $statusLabel;
                }
            }

            if ($this->showDtb) {
                $data[] = $this->formatDecimal($tongKet['diem_trung_binh'] ?? null, '');
            }

            if ($this->showKetQua) {
                $ketQua = $tongKet['ket_qua'] ?? 'hoan_thanh';
                $data[] = $ketQua === 'khong_hoan_thanh' ? 'Không hoàn thành' : 'Hoàn thành';
            }

            if ($this->showDanhGia) {
                $hasDanhGia = (bool)($tongKet['has_danh_gia'] ?? false);
                $noiDung = $hasDanhGia ? trim((string) ($tongKet['danh_gia_ren_luyen'] ?? '')) : '';
                $data[] = $noiDung !== '' ? $noiDung : 'Không đánh giá';
            }

            if ($this->showHanhDong) {
                $data[] = $this->showKetQua ? ($tongKet['ket_qua'] === 'hoan_thanh' ? 'Đóng' : 'Sửa') : '';
            }

            $rows[] = $data;
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
}
