<?php

namespace App\Filament\Resources\HocVienResource\Pages;

use App\Filament\Resources\HocVienResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHocVien extends CreateRecord
{
    protected static string $resource = HocVienResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Thêm học viên mới thành công';
    }

    // Sau khi tạo xong, quay về list
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
