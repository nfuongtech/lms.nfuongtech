<?php

namespace App\Filament\Resources\GiangVienResource\Pages;

use App\Filament\Resources\GiangVienResource;
use App\Filament\Exports\GiangVienExport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGiangViens extends ListRecords
{
    protected static string $resource = GiangVienResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Xuất Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->button()
                ->color('gray') // màu trung tính
                ->extraAttributes([
                    'class' => 'bg-white text-black border border-gray-300 hover:bg-gray-100',
                ])
                ->action(fn () => (new GiangVienExport())->download('giangvien.xlsx')),
            Actions\CreateAction::make()->label('Tạo Giảng viên'),
        ];
    }
}
