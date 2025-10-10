<?php

namespace App\Filament\Resources\TuyChonKetQuaResource\Pages;

use App\Filament\Resources\TuyChonKetQuaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTuyChonKetQuas extends ListRecords
{
    protected static string $resource = TuyChonKetQuaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
