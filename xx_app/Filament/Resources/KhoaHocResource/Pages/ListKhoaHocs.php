<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Resources\Pages\ListRecords;

class ListKhoaHocs extends ListRecords
{
    protected static string $resource = KhoaHocResource::class;
    protected static ?string $title = 'Kế hoạch đào tạo';
}
