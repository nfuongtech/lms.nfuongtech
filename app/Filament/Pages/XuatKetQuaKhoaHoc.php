<?php

namespace App\Filament\Pages;

use App\Exports\KetQuaKhoaHocExport;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class XuatKetQuaKhoaHoc extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'Báo cáo';
    protected static ?string $title = 'Xuất kết quả khóa học';
    protected static string $view = 'filament.pages.xuat-ket-qua-khoa-hoc';

    public $filters = [
        'khoa_hoc_id' => null,
        'ket_qua' => null,
        'trang_thai_hoc_vien' => null,
    ];

    public static function getSlug(): string
    {
        return 'xuat-ket-qua-khoa-hoc';
    }

    public function export(): BinaryFileResponse
    {
        $fileName = 'ket_qua_khoa_hoc_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new KetQuaKhoaHocExport($this->filters), $fileName);
    }
}
