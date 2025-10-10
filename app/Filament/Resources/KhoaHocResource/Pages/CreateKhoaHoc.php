<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKhoaHoc extends CreateRecord
{
    protected static string $resource = KhoaHocResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // CSDL không có cột che_do_ma_khoa
        unset($data['che_do_ma_khoa']);
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Sau khi tạo -> mở trang Sửa để có bảng "Lịch đào tạo" kèm nút "Thêm"
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Lưu'),
            $this->getCancelFormAction()->label('Hủy'),
        ];
    }
}
