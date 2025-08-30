<?php

namespace App\Filament\Resources\GiangVienResource\Pages;

use App\Filament\Resources\GiangVienResource;
use App\Filament\Exports\GiangVienExporter;
use App\Filament\Imports\GiangVienImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGiangViens extends ListRecords
{
    protected static string $resource = GiangVienResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()->label('Xuất Excel')->exporter(GiangVienExporter::class),
            Actions\ImportAction::make()->importer(GiangVienImporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
