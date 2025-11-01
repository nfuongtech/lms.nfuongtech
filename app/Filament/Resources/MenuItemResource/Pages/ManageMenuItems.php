<?php

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Resources\MenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMenuItems extends ManageRecords
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Thêm menu mới')
                ->icon('heroicon-o-plus')
                ->modalWidth('lg'),
        ];
    }
}
