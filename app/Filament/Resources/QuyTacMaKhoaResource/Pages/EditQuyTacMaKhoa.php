<?php

namespace App\Filament\Resources\QuyTacMaKhoaResource\Pages;

use App\Filament\Resources\QuyTacMaKhoaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuyTacMaKhoa extends EditRecord
{
    protected static string $resource = QuyTacMaKhoaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
