<?php
namespace App\Filament\Resources\LichHocResource\Pages;

use App\Filament\Resources\LichHocResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListLichHocs extends ListRecords
{
    protected static string $resource = LichHocResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
