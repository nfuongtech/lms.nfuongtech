<?php

namespace App\Filament\Exports;

use App\Models\KhoaHoc;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BcChuyenDeExporter implements FromCollection, WithHeadings, ShouldAutoSize
{
    public $month;
    public $year;
    public $khoaHocId;

    public function __construct($month, $year, $khoaHocId)
    {
        $this->month = $month;
        $this->year = $year;
        $this->khoaHocId = $khoaHocId;
    }

    public function collection()
    {
        $query = KhoaHoc::query()->with([
            'chuyenDe',
            'lichHocs.giangVien',
            'dangKys.diemDanhs',
            'dangKys.ketQuaKhoaHoc'
        ]);

        if ($this->khoaHocId) {
            $query->where('id', $this->khoaHocId);
        }
        if ($this->year) {
            $query->where('nam', $this->year);
        }
        if ($this->month) {
            $query->whereHas('lichHocs', function ($q) {
                $q->whereMonth('ngay_hoc', $this->month);
            });
        }

        return $query->get()->map(function ($khoaHoc, $key) {
            $dangKys = $khoaHoc->dangKys;
            $lichHocs = $khoaHoc->lichHocs;

            $co_mat = 0;
            $phep = 0;
            $khong_phep = 0;
            $dat_yeu_cau = 0;

            foreach ($dangKys as $dk) {
                $co_mat += $dk->diemDanhs->where('trang_thai', 'Có mặt')->count();
                $phep += $dk->diemDanhs->where('trang_thai', 'Phép')->count();
                $khong_phep += $dk->diemDanhs->where('trang_thai', 'Không phép')->count();
                if ($dk->ketQuaKhoaHoc && in_array($dk->ketQuaKhoaHoc->ket_qua, ['Hoàn thành', 'Đạt yêu cầu'])) {
                    $dat_yeu_cau++;
                }
            }

            return [
                'stt' => $key + 1,
                'ten_chuyen_de' => $khoaHoc->chuyenDe->ten_chuyen_de,
                'lop_khoa' => $khoaHoc->ten_khoa_hoc,
                'giang_vien' => $lichHocs->pluck('giangVien.ho_ten')->filter()->unique()->implode(', '),
                'thoi_gian_dao_tao' => $lichHocs->pluck('ngay_hoc')->map(fn($date) => \Carbon\Carbon::parse($date)->format('d/m/Y'))->unique()->implode(', '),
                'so_luong' => $dangKys->count(),
                'co_mat' => $co_mat,
                'phep' => $phep,
                'khong_phep' => $khong_phep,
                'dat_yeu_cau' => $dat_yeu_cau,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'TT',
            'Tên Chuyên đề',
            'Khóa/Lớp',
            'Giảng viên',
            'Thời gian đào tạo',
            'Số lượng',
            'Chuyên cần: Có mặt',
            'Chuyên cần: Phép',
            'Chuyên cần: Không phép',
            'Kết quả: Đạt',
        ];
    }
}
