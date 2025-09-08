<?php

namespace App\Filament\Resources\DonViResource\Pages;

use App\Filament\Resources\DonViResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDonVi extends EditRecord
{
    protected static string $resource = DonViResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
