<?php

namespace App\Filament\Resources\HocVienHoanThanhResource\Pages;

use App\Filament\Resources\HocVienHoanThanhResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHocVienHoanThanh extends EditRecord
{
    protected static string $resource = HocVienHoanThanhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
