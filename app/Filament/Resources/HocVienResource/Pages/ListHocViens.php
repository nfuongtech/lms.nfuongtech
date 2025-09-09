<?php

namespace App\Filament\Resources\HocVienResource\Pages;

use App\Filament\Resources\HocVienResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
// --- Bắt đầu: Thêm cho thống kê ---
use Illuminate\Contracts\View\View;
// --- Kết thúc: Thêm cho thống kê ---

class ListHocViens extends ListRecords
{
    protected static string $resource = HocVienResource::class;

    // --- Bắt đầu: Thêm phương thức render() để hiển thị thống kê ---
    public function render(): View
    {
        // Gọi phương thức từ Resource để lấy dữ liệu thống kê
        $thongKe = HocVienResource::getThongKeTheoDonVi();

        return view(static::$view, [
            'records' => $this->getFilteredTableQuery()->paginate($this->getTableRecordsPerPage()),
            'thong_ke' => $thongKe, // Truyền dữ liệu thống kê sang view
        ]);
    }
    // --- Kết thúc: Thêm phương thức render() ---
}
