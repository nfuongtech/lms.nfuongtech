<?php

namespace App\Filament\Exports;

use App\Models\KhoaHoc;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KhoaHocExport implements FromCollection, WithHeadings
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = KhoaHoc::with('chuongTrinh');

        if (!empty($this->filters['trang_thai'])) {
            $query->where('trang_thai', $this->filters['trang_thai']);
        }
        if (!empty($this->filters['nam'])) {
            $query->where('nam', $this->filters['nam']);
        }

        return $query->get()->map(fn ($k) => [
            'ma_khoa_hoc' => $k->ma_khoa_hoc,
            'chuong_trinh' => $k->chuongTrinh->ten_chuong_trinh ?? '',
            'nam' => $k->nam,
            'trang_thai' => is_object($k->trang_thai) ? $k->trang_thai->label() : (\App\Enums\TrangThaiKhoaHoc::tryFrom($k->trang_thai)?->label() ?? $k->trang_thai),
            'phien_ban_thay_doi' => $k->phien_ban_thay_doi ?? 0,
            'so_buoi' => $k->lichHocs()->count(),
            'so_hoc_vien' => $k->dangKys()->count(),
        ]);
    }

    public function headings(): array
    {
        return [
            'Mã khóa học',
            'Chương trình',
            'Năm',
            'Trạng thái',
            'Phiên bản (số lần chỉnh sửa)',
            'Số buổi',
            'Số học viên',
        ];
    }
}
