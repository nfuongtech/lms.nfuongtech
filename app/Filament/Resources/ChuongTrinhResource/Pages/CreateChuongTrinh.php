<?php

namespace App\Filament\Resources\ChuongTrinhResource\Pages;

use App\Filament\Resources\ChuongTrinhResource;
use App\Models\ChuongTrinh;
use App\Models\QuyTacMaKhoa;
use Filament\Resources\Pages\CreateRecord;

class CreateChuongTrinh extends CreateRecord
{
    protected static string $resource = ChuongTrinhResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Logic tự động tạo mã chương trình
        $quyTac = QuyTacMaKhoa::where('loai_hinh_dao_tao', 'Chương trình Đào tạo')->first();
        $prefix = $quyTac ? $quyTac->tien_to : 'CT'; // Fallback prefix is 'CT'

        $lastRecord = ChuongTrinh::where('ma_chuong_trinh', 'like', "{$prefix}-%")->latest('ma_chuong_trinh')->first();

        $nextNumber = 1;
        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->ma_chuong_trinh, -3);
            $nextNumber = $lastNumber + 1;
        }

        $sequence = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $data['ma_chuong_trinh'] = "{$prefix}-{$sequence}";

        return $data;
    }
}
