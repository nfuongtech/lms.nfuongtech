<?php

namespace App\Filament\Resources\DiaDiemDaoTaoResource\Pages;

use App\Filament\Resources\DiaDiemDaoTaoResource;
use Filament\Resources\Pages\ListRecords;

class ListDiaDiemDaoTaos extends ListRecords
{
    protected static string $resource = DiaDiemDaoTaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
