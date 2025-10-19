<?php

namespace App\Exports;

use App\Models\KetQuaKhoaHoc;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KetQuaKhoaHocExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = KetQuaKhoaHoc::with(['dangKy.hocVien', 'dangKy.khoaHoc.chuongTrinh']);

        if (!empty($this->filters['khoa_hoc_id'])) {
            $query->whereHas('dangKy', function ($q) {
                $q->where('khoa_hoc_id', $this->filters['khoa_hoc_id']);
            });
        }

        if (!empty($this->filters['ket_qua'])) {
            $query->where('ket_qua', $this->filters['ket_qua']);
        }

        if (!empty($this->filters['trang_thai_hoc_vien'])) {
            $query->where('trang_thai_hoc_vien', $this->filters['trang_thai_hoc_vien']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'MSNV',
            'Họ tên',
            'Khóa học',
            'Chương trình',
            'Điểm trung bình',
            'Kết quả',
            'Trạng thái học viên',
            'Chi phí',
        ];
    }

    public function map($ketQua): array
    {
        return [
            $ketQua->dangKy->hocVien->msnv ?? '',
            $ketQua->dangKy->hocVien->ho_ten ?? '',
            $ketQua->dangKy->khoaHoc->ma_khoa_hoc ?? '',
            $ketQua->dangKy->khoaHoc->chuongTrinh->ten_chuong_trinh ?? '',
            $ketQua->diem_trung_binh,
            $this->formatKetQua($ketQua->ket_qua),
            $this->formatTrangThai($ketQua->trang_thai_hoc_vien),
            $ketQua->chi_phi,
        ];
    }

    private function formatKetQua($ketQua)
    {
        return match($ketQua) {
            'hoan_thanh' => 'Hoàn thành',
            'khong_hoan_thanh' => 'Không hoàn thành',
            'dat_yeu_cau' => 'Đạt yêu cầu',
            'khong_dat_yeu_cau' => 'Không đạt yêu cầu',
            default => $ketQua
        };
    }

    private function formatTrangThai($trangThai)
    {
        return match($trangThai) {
            'hoan_thanh' => 'Hoàn thành',
            'khong_hoan_thanh' => 'Không hoàn thành',
            default => $trangThai
        };
    }
}
