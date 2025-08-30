<?php

namespace App\Filament\Resources\GiangVienResource\Pages;

use App\Filament\Resources\GiangVienResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGiangVien extends EditRecord
{
    protected static string $resource = GiangVienResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
