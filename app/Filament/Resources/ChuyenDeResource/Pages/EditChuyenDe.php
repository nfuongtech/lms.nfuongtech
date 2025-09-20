<?php

namespace App\Filament\Resources\ChuyenDeResource\Pages;

use App\Filament\Resources\ChuyenDeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChuyenDe extends EditRecord
{
    protected static string $resource = ChuyenDeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getDeletedRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
