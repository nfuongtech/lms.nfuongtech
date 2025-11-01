<?php

namespace App\Filament\Resources\AdminNavigationItemResource\Pages;

use App\Filament\Resources\AdminNavigationItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdminNavigationItems extends ListRecords
{
    protected static string $resource = AdminNavigationItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
