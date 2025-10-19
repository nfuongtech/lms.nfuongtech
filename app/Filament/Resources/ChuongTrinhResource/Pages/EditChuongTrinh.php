<?php

namespace App\Filament\Resources\ChuongTrinhResource\Pages;

use App\Filament\Resources\ChuongTrinhResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChuongTrinh extends EditRecord
{
    protected static string $resource = ChuongTrinhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
