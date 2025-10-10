<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterSave(): void
    {
        // Đồng bộ hóa tên của Giảng viên nếu có
        if ($this->record->giangVien) {
            $this->record->giangVien->update(['ho_ten' => $this->record->name]);
        }
    }
}
