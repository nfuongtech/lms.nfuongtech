<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKhoaHoc extends CreateRecord
{
    protected static string $resource = KhoaHocResource::class;

    public function getTitle(): string
    {
        return 'Tạo kế hoạch đào tạo';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Tạo'),
            $this->getCancelFormAction()->label('Hủy'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Không ghi cờ hỗ trợ vào DB
        unset($data['che_do_ma_khoa']);
        return $data;
    }

    /**
     * Sau khi tạo → chuyển sang trang Sửa để hiển thị ngay bảng "Lịch đào tạo"
     * (nút "Tạo lịch đào tạo" sẽ có sẵn ở RelationManager)
     */
    protected function afterCreate(): void
    {
        $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
    }
}
