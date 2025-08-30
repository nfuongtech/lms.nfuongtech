<?php

namespace App\Filament\Resources\TuyChonKetQuaResource\Pages;

use App\Filament\Resources\TuyChonKetQuaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTuyChonKetQua extends EditRecord
{
    protected static string $resource = TuyChonKetQuaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
