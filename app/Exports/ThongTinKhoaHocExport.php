<?php

namespace App\Exports;

use App\Models\KhoaHoc;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ThongTinKhoaHocExport implements FromArray, WithHeadings, WithStyles
{
    protected $khoaHocId;

    public function __construct($khoaHocId)
    {
        $this->khoaHocId = $khoaHocId;
    }

    public function array(): array
    {
        $khoaHoc = KhoaHoc::with('chuongTrinh', 'lichHocs.giangVien')->find($this->khoaHocId);

        if (!$khoaHoc) {
            return [];
        }

        $data = [
            ['Thông tin Khóa học'],
            ['Mã khóa học:', $khoaHoc->ma_khoa_hoc],
            ['Tên chương trình:', $khoaHoc->chuongTrinh->ten_chuong_trinh ?? 'N/A'],
            ['Năm:', $khoaHoc->nam ?? 'N/A'],
            ['Trạng thái:', $khoaHoc->trang_thai],
            [''],
            ['Lịch học'],
            ['Ngày học', 'Giờ bắt đầu', 'Giờ kết thúc', 'Chuyên đề', 'Giảng viên', 'Địa điểm']
        ];

        foreach ($khoaHoc->lichHocs as $lich) {
            $data[] = [
                $lich->ngay_hoc ? date('d/m/Y', strtotime($lich->ngay_hoc)) : 'N/A',
                $lich->gio_bat_dau ? date('H:i', strtotime($lich->gio_bat_dau)) : 'N/A',
                $lich->gio_ket_thuc ? date('H:i', strtotime($lich->gio_ket_thuc)) : 'N/A',
                $lich->chuyenDe->ten_chuyen_de ?? 'N/A',
                $lich->giangVien->ho_ten ?? 'N/A',
                $lich->dia_diem ?? 'N/A',
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        // Headings are handled in array() for this complex structure
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold
            1    => ['font' => ['bold' => true]],
            2    => ['font' => ['bold' => true]],
            3    => ['font' => ['bold' => true]],
            4    => ['font' => ['bold' => true]],
            5    => ['font' => ['bold' => true]],
            7    => ['font' => ['bold' => true]], // Header lịch học
            8    => ['font' => ['bold' => true]], // Header cột lịch học
        ];
    }
}
