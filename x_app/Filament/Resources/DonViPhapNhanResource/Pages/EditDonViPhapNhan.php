<?php

namespace App\Filament\Resources\DonViPhapNhanResource\Pages;

use App\Filament\Resources\DonViPhapNhanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDonViPhapNhan extends EditRecord
{
    protected static string $resource = DonViPhapNhanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
