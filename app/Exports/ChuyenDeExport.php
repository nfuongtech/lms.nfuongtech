<?php

namespace App\Exports;

use App\Models\ChuyenDe;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ChuyenDeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private int $index = 0;

    public function collection()
    {
        return ChuyenDe::with(['giangViens'])->orderBy('id')->get();
    }

    public function headings(): array
    {
        return [
            'TT',
            'Mã số',
            'Tên Chuyên đề/Học phần',
            'Thời lượng (giờ)',
            'Đối tượng đào tạo',
            'Giảng viên',
            'Mục tiêu',
            'Nội dung',
            'Số lượng tài liệu',
            'Trạng thái tài liệu',
        ];
    }

    public function map($chuyenDe): array
    {
        $this->index++;

        $giangViens = $chuyenDe->giangViens->pluck('ho_ten')->implode(', ');

        $soLuongTaiLieu = !empty($chuyenDe->bai_giang_path) ? count($chuyenDe->bai_giang_path) : 'Không có';

        return [
            $this->index,
            $chuyenDe->ma_so,
            $chuyenDe->ten_chuyen_de,
            // đảm bảo định dạng "2,5"
            str_replace('.', ',', number_format($chuyenDe->thoi_luong, 1)),
            $chuyenDe->doi_tuong_dao_tao,
            $giangViens,
            $chuyenDe->muc_tieu,
            $chuyenDe->noi_dung,
            $soLuongTaiLieu,
            $chuyenDe->trang_thai_tai_lieu,
        ];
    }
}
