<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KhoaHocResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKhoaHoc extends CreateRecord
{
    protected static string $resource = KhoaHocResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Tạo khóa học thành công!';
    }
}
