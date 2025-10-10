<?php

namespace App\Exports;

use App\Models\DiemDanh;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DiemDanhExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DiemDanh::with(['dangKy.hocVien', 'lichHoc.khoaHoc.chuongTrinh'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'MSNV',
            'Họ và tên',
            'Ngày học',
            'Giờ bắt đầu',
            'Giờ kết thúc',
            'Trạng thái',
            'Lý do vắng',
            'Điểm buổi học',
            'Mã khóa học',
            'Tên chương trình',
        ];
    }

    /**
     * @param DiemDanh $diemDanh
     * @return array
     */
    public function map($diemDanh): array
    {
        return [
            $diemDanh->dangKy->hocVien->msnv ?? 'N/A',
            $diemDanh->dangKy->hocVien->ho_ten ?? 'N/A',
            $diemDanh->lichHoc->ngay_hoc ? date('d/m/Y', strtotime($diemDanh->lichHoc->ngay_hoc)) : 'N/A',
            $diemDanh->lichHoc->gio_bat_dau ? date('H:i', strtotime($diemDanh->lichHoc->gio_bat_dau)) : 'N/A',
            $diemDanh->lichHoc->gio_ket_thuc ? date('H:i', strtotime($diemDanh->lichHoc->gio_ket_thuc)) : 'N/A',
            match($diemDanh->trang_thai) {
                'co_mat' => 'Có mặt',
                'vang_phep' => 'Vắng phép',
                'vang_khong_phep' => 'Vắng không phép',
                default => $diemDanh->trang_thai,
            },
            $diemDanh->ly_do_vang ?? 'N/A',
            $diemDanh->diem_buoi_hoc ?? 'N/A',
            $diemDanh->lichHoc->khoaHoc->ma_khoa_hoc ?? 'N/A',
            $diemDanh->lichHoc->khoaHoc->chuongTrinh->ten_chuong_trinh ?? 'N/A',
        ];
    }
}
