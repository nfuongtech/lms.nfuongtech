<?php

namespace App\Filament\Resources\DonViResource\Pages;

use App\Filament\Resources\DonViResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDonVis extends ListRecords
{
    protected static string $resource = DonViResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tạo đơn vị'),
        ];
    }
}
