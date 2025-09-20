<?php

namespace App\Filament\Resources\TuyChonKetQuaResource\Pages;

use App\Filament\Resources\TuyChonKetQuaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateTuyChonKetQua extends CreateRecord
{
    protected static string $resource = TuyChonKetQuaResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Tạo Tùy chọn Kết quả mới thành công')
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
