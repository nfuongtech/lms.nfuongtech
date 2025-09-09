<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKhoaHoc extends ViewRecord
{
    protected static string $resource = KhoaHocResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
