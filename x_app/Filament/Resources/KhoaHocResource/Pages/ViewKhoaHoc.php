<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Resources\Pages\ViewRecord;

class ViewKhoaHoc extends ViewRecord
{
    protected static string $resource = KhoaHocResource::class;

    public function getTitle(): string
    {
        return 'Kế hoạch đào tạo';
    }
}
