<?php
namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Resources\Pages\EditRecord;

class EditKhoaHoc extends EditRecord
{
    protected static string $resource = KhoaHocResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // nếu cần kiểm tra / điều chỉnh dữ liệu trước khi lưu
        return $data;
    }
}
