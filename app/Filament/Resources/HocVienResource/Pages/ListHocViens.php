<?php

namespace App\Filament\Resources\HocVienResource\Pages;

use App\Filament\Resources\HocVienResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHocViens extends ListRecords
{
    protected static string $resource = HocVienResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tạo học viên'),
        ];
    }
}
