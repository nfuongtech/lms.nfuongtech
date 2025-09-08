<?php

namespace App\Filament\Resources\ChuyenDeResource\Pages;

use App\Filament\Resources\ChuyenDeResource;
use App\Filament\Imports\ChuyenDeImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChuyenDes extends ListRecords
{
    protected static string $resource = ChuyenDeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Thêm nút Import trực tiếp vào trang
            Actions\ImportAction::make()
                ->importer(ChuyenDeImporter::class),
            // Giữ lại nút tạo mới mặc định
            Actions\CreateAction::make(),
        ];
    }
}
