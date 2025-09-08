<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use App\Models\KhoaHoc;
use Filament\Resources\Pages\CreateRecord;

class CreateKhoaHoc extends CreateRecord
{
    protected static string $resource = KhoaHocResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Sinh mã khóa: KH-YYMM-xxx
        $prefix = 'KH';
        $datePart = now()->format('ym'); // ví dụ 2509
        $count = KhoaHoc::where('ma_khoa', 'like', "{$prefix}-{$datePart}-%")->count() + 1;
        $data['ma_khoa'] = "{$prefix}-{$datePart}-" . str_pad($count, 3, '0', STR_PAD_LEFT);

        return $data;
    }
}
