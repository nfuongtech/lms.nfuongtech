<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Resources\Pages\EditRecord;

class EditKhoaHoc extends EditRecord
{
    protected static string $resource = KhoaHocResource::class;
    protected static ?string $title = 'Sửa Kế hoạch đào tạo';

    protected function getSaveFormActionLabel(): ?string
    {
        return 'Lưu';
    }
}
