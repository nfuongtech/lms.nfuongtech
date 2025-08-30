<?php

namespace App\Filament\Resources\DangKyResource\Pages;

use App\Filament\Resources\DangKyResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDangKy extends CreateRecord
{
    protected static string $resource = DangKyResource::class;

    /**
     * Override the default creation handler to create multiple records.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $khoaHocId = $data['khoa_hoc_id'];
        $hocVienIds = $data['hoc_vien_id'];
        $lastRecord = null;

        foreach ($hocVienIds as $hocVienId) {
            $lastRecord = static::getModel()::firstOrCreate([
                'khoa_hoc_id' => $khoaHocId,
                'hoc_vien_id' => $hocVienId,
            ]);
        }

        Notification::make()
            ->title('Ghi danh thành công')
            ->body('Đã ghi danh thành công ' . count($hocVienIds) . ' học viên.')
            ->success()
            ->send();

        // The method must return a Model instance. We'll return the last one created.
        return $lastRecord;
    }

    /**
     * Redirect to the index page after creation.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
