<?php

namespace App\Filament\Resources\HocVienHoanThanhResource\Pages;

use App\Filament\Resources\HocVienHoanThanhResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHocVienHoanThanhs extends ListRecords
{
    protected static string $resource = HocVienHoanThanhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
