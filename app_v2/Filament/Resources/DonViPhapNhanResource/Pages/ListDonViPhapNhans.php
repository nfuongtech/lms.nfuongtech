<?php

namespace App\Filament\Resources\DonViPhapNhanResource\Pages;

use App\Filament\Resources\DonViPhapNhanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDonViPhapNhans extends ListRecords
{
    protected static string $resource = DonViPhapNhanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
