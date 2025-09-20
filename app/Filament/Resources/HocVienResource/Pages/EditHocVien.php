<?php

namespace App\Filament\Resources\HocVienResource\Pages;

use App\Filament\Resources\HocVienResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditHocVien extends EditRecord
{
    protected static string $resource = HocVienResource::class;

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Cập nhật học viên thành công')
            ->success();
    }

    // Sau khi lưu, quay về list
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
