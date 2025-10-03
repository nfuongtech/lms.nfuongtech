<?php

namespace App\Filament\Resources\HocVienHoanThanhResource\Pages;

use App\Filament\Resources\HocVienHoanThanhResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListHocVienHoanThanhs extends ListRecords
{
    protected static string $resource = HocVienHoanThanhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Thêm'),
        ];
    }
}
