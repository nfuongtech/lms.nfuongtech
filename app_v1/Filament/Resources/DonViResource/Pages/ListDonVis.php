<?php

namespace App\Filament\Resources\DonViResource\Pages;

use App\Filament\Resources\DonViResource;
use App\Filament\Imports\DonViImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDonVis extends ListRecords
{
    protected static string $resource = DonViResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->importer(DonViImporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
