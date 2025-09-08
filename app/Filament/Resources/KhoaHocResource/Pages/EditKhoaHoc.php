<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditKhoaHoc extends EditRecord
{
    protected static string $resource = KhoaHocResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Xóa'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Cập nhật khóa học thành công!';
    }
}
