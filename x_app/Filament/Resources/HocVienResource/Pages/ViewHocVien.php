<?php

namespace App\Filament\Resources\HocVienResource\Pages;

use App\Filament\Resources\HocVienResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHocVien extends ViewRecord
{
    protected static string $resource = HocVienResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
