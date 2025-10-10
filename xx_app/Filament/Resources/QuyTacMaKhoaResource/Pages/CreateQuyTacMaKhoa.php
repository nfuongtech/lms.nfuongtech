<?php

namespace App\Filament\Resources\QuyTacMaKhoaResource\Pages;

use App\Filament\Resources\QuyTacMaKhoaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateQuyTacMaKhoa extends CreateRecord
{
    protected static string $resource = QuyTacMaKhoaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
