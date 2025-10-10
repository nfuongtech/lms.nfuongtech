<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKhoaHoc extends CreateRecord
{
    protected static string $resource = KhoaHocResource::class;
    protected static ?string $title = 'Tạo kế hoạch đào tạo';

    protected function getCreateFormActionLabel(): ?string
    {
        return 'Tạo';
    }
}

