<?php

namespace App\Filament\Resources\QuyTacMaKhoaResource\Pages;

use App\Filament\Resources\QuyTacMaKhoaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuyTacMaKhoas extends ListRecords
{
    protected static string $resource = QuyTacMaKhoaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Thêm quy tắc mới'),
        ];
    }
}
