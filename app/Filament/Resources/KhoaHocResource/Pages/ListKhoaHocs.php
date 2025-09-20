<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use App\Exports\KhoaHocExport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class ListKhoaHocs extends ListRecords
{
    protected static string $resource = KhoaHocResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Nút Xuất Excel
            Actions\Action::make('export_excel')
                ->label('Xuất Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    // Lấy query đã áp dụng filter
                    $query = $this->getFilteredTableQuery();

                    // Lấy collection bản ghi
                    $records = $query->get();

                    if ($records instanceof Collection) {
                        return Excel::download(new KhoaHocExport($records), 'khoa_hoc_export.xlsx');
                    }

                    return null;
                }),

            // Nút tạo khóa học
            Actions\CreateAction::make()
                ->label('Tạo khóa học'),
        ];
    }
}
