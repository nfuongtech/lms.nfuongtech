<?php

namespace App\Filament\Resources\DonViResource\Pages;

use App\Filament\Resources\DonViResource;
use App\Models\DonVi;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateDonVi extends CreateRecord
{
    protected static string $resource = DonViResource::class;

    /**
     * Tự động tạo mã và kiểm tra trùng lặp trước khi tạo.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Kiểm tra xem "Tên hiển thị" đã tồn tại hay chưa
        $existingRecord = DonVi::where('phong_bo_phan', $data['phong_bo_phan'])
            ->where('cong_ty_ban_nvqt', $data['cong_ty_ban_nvqt'])
            ->where('thaco_tdtv', $data['thaco_tdtv'])
            ->first();

        if ($existingRecord) {
            throw ValidationException::withMessages([
                'data.thaco_tdtv' => 'Đơn vị với Tên hiển thị này đã tồn tại.',
                'data.cong_ty_ban_nvqt' => 'Đơn vị với Tên hiển thị này đã tồn tại.',
                'data.phong_bo_phan' => 'Đơn vị với Tên hiển thị này đã tồn tại.',
            ]);
        }

        // Tự động tạo mã đơn vị
        $today = now()->format('Ymd');
        $lastRecord = DonVi::where('ma_don_vi', 'like', "{$today}-%")->latest('ma_don_vi')->first();

        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->ma_don_vi, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $data['ma_don_vi'] = $today . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        return $data;
    }

    /**
     * Xác định URL để chuyển hướng đến sau khi tạo thành công.
     */
    protected function getRedirectUrl(): string
    {
        // Đây là hành vi mặc định của Filament, chúng ta định nghĩa lại để đảm bảo nó luôn đúng.
        return $this->getResource()::getUrl('index');
    }
}
