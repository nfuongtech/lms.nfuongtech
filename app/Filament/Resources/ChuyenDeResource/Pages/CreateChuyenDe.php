<?php

namespace App\Filament\Resources\ChuyenDeResource\Pages;

use App\Filament\Resources\ChuyenDeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChuyenDe extends CreateRecord
{
    protected static string $resource = ChuyenDeResource::class;

    protected function getCreatedRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
