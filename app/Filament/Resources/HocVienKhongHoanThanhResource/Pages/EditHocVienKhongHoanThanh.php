<?php

namespace App\Filament\Resources\HocVienKhongHoanThanhResource\Pages;

use App\Filament\Resources\HocVienKhongHoanThanhResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHocVienKhongHoanThanh extends EditRecord
{
    protected static string $resource = HocVienKhongHoanThanhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
