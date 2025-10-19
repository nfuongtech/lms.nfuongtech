<?php

namespace App\Filament\Resources\ChuongTrinhResource\Pages;

use App\Filament\Resources\ChuongTrinhResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChuongTrinhs extends ListRecords
{
    protected static string $resource = ChuongTrinhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
