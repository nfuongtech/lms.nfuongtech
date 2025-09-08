<?php

namespace App\Filament\Resources\DangKyResource\Pages;

use App\Filament\Resources\DangKyResource;
use App\Filament\Resources\HocVienResource;
use App\Models\HocVien;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDangKy extends CreateRecord
{
    protected static string $resource = DangKyResource::class;
    
    protected function beforeCreate(): void
    {
        $data = $this->form->getState();
        $hocVienIds = $data['hoc_vien_id'];
        
        $invalidHocViens = HocVien::query()
            ->whereIn('id', $hocVienIds)
            ->where('tinh_trang', '!=', 'Đang làm việc')
            ->get();
            
        if ($invalidHocViens->isNotEmpty()) {
            $invalidNames = $invalidHocViens->pluck('ho_ten')->implode(', ');

            Notification::make()
                ->title('Cảnh báo: Có học viên không hợp lệ')
                ->body("Các học viên sau không ở trạng thái 'Đang làm việc': {$invalidNames}")
                ->danger()
                ->persistent() // Thông báo sẽ không tự đóng
                ->actions([
                    // Nút hành động để bỏ qua và tiếp tục
                    Action::make('skip_and_create')
                        ->label('Bỏ qua & Tiếp tục ghi danh')
                        ->color('primary')
                        ->action(function () use ($data, $invalidHocViens) {
                            $validHocVienIds = array_diff($data['hoc_vien_id'], $invalidHocViens->pluck('id')->toArray());
                            if (empty($validHocVienIds)) {
                                Notification::make()->title('Không có học viên hợp lệ nào để ghi danh.')->warning()->send();
                                return;
                            }
                            $this->form->fill(['hoc_vien_id' => $validHocVienIds]);
                            $this->create(false);
                        }),
                    // Nút hành động để yêu cầu sửa
                    Action::make('edit_hoc_vien')
                        ->label('Tới trang Học viên để sửa')
                        ->color('gray')
                        ->url(HocVienResource::getUrl('index'), shouldOpenInNewTab: true),
                ])
                ->send();

            // Dừng hành động tạo mặc định
            $this->halt();
        }
    }

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

        return $lastRecord;
    }
}
