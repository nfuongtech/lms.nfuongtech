<?php

namespace App\Filament\Resources\HocVienResource\Pages;

use App\Filament\Resources\HocVienResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHocVien extends EditRecord
{
    protected static string $resource = HocVienResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
