<?php

namespace App\Filament\Pages;

use App\Filament\Exports\BcChuyenDeExporter;
use App\Models\KhoaHoc;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;

class BcChuyenDe extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.pages.bc-chuyen-de';
    protected static ?string $navigationGroup = 'Báo cáo';
    protected static ?string $title = 'Báo cáo Chuyên đề';
    protected static ?string $navigationLabel = 'BC Chuyên đề';

    public ?int $filterMonth = null;
    public ?int $filterYear = null;
    public ?int $filterKhoaHoc = null;
    public array $reportData = [];

    public function mount(): void
    {
        $this->filterYear = now()->year;
        $this->generateReport();
    }

    public function generateReport(): void
    {
        // Bắt đầu truy vấn từ KhoaHoc
        $query = KhoaHoc::query()->with([
            'chuyenDe',
            'lichHocs.giangVien',
            'dangKys.diemDanhs',
            'dangKys.ketQuaKhoaHoc'
        ]);

        // Áp dụng bộ lọc
        if ($this->filterKhoaHoc) {
            $query->where('id', $this->filterKhoaHoc);
        }
        if ($this->filterYear) {
            $query->where('nam', $this->filterYear);
        }
        if ($this->filterMonth) {
            $query->whereHas('lichHocs', function ($q) {
                $q->whereMonth('ngay_hoc', $this->filterMonth);
            });
        }

        $khoaHocs = $query->get();
        $data = [];

        // Xử lý và tính toán dữ liệu
        foreach ($khoaHocs as $khoaHoc) {
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

            $data[] = [
                'ten_chuyen_de' => $khoaHoc->chuyenDe->ten_chuyen_de,
                'lop_khoa' => $khoaHoc->ten_khoa_hoc,
                'giang_viens' => $lichHocs->pluck('giangVien.ho_ten')->filter()->unique()->implode(', '),
                'thoi_gian_dao_tao' => $lichHocs->pluck('ngay_hoc')->map(fn($date) => \Carbon\Carbon::parse($date)->format('d/m/Y'))->unique()->implode(', '),
                'so_luong' => $dangKys->count(),
                'co_mat' => $co_mat,
                'phep' => $phep,
                'khong_phep' => $khong_phep,
                'dat_yeu_cau' => $dat_yeu_cau,
            ];
        }

        $this->reportData = $data;
    }

    public function export()
    {
        return Excel::download(new BcChuyenDeExporter($this->filterMonth, $this->filterYear, $this->filterKhoaHoc), 'bao_cao_chuyen_de.xlsx');
    }

    public function getKhoaHocOptions(): array
    {
        return KhoaHoc::query()
            ->orderBy('nam', 'desc')
            ->orderBy('ten_khoa_hoc')
            ->get()
            ->mapWithKeys(fn ($kh) => [$kh->id => $kh->ten_khoa_hoc . ' (' . $kh->nam . ')'])
            ->toArray();
    }

    public function getMonths(): array
    {
        return array_reduce(range(1, 12), fn($carry, $month) => $carry + [$month => "Tháng {$month}"], []);
    }

    public function getYears(): array
    {
        $currentYear = now()->year;
        return array_combine(range($currentYear, $currentYear - 5), range($currentYear, $currentYear - 5));
    }
}
