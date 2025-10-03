<?php

namespace App\Filament\Resources\HocVienKhongHoanThanhResource\Pages;

use App\Filament\Resources\HocVienKhongHoanThanhResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListHocVienKhongHoanThanhs extends ListRecords
{
    protected static string $resource = HocVienKhongHoanThanhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Thêm'),
        ];
    }
}
