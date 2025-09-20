<?php

namespace App\Filament\Resources\TuyChonKetQuaResource\Pages;

use App\Filament\Resources\TuyChonKetQuaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditTuyChonKetQua extends EditRecord
{
    protected static string $resource = TuyChonKetQuaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Xóa Tùy chọn Kết quả')
                ->modalDescription('Bạn có chắc chắn muốn xóa tùy chọn này? Hành động này không thể hoàn tác.')
                ->action(function () {
                    $record = $this->getRecord();

                    // Kiểm tra xem có bản ghi nào đang dùng tùy chọn này không
                    $count = 0;

                    // Ví dụ: Kiểm tra trong bảng hoc_viens (cột tinh_trang)
                    if ($record->loai === 'tinh_trang_hoc_vien') {
                        $count += \App\Models\HocVien::where('tinh_trang', $record->gia_tri)->count();
                    }

                    // Ví dụ: Kiểm tra trong bảng ket_qua_khoa_hocs (cột ket_qua)
                    if ($record->loai === 'ket_qua') {
                        $count += \App\Models\KetQuaKhoaHoc::where('ket_qua', $record->gia_tri)->count();
                    }

                    // Ví dụ: Kiểm tra trong bảng dang_kies (cột ly_do_vang)
                    if ($record->loai === 'ly_do_vang') {
                        $count += \App\Models\DangKy::where('ly_do_vang', $record->gia_tri)->count();
                    }

                    // Thêm các bảng khác nếu cần
                    // ...

                    if ($count > 0) {
                        // Không cho phép xóa nếu có bản ghi đang dùng
                        Notification::make()
                            ->title('Không thể xóa')
                            ->body("Tùy chọn này đang được sử dụng bởi $count bản ghi. Vui lòng cập nhật các bản ghi trước khi xóa.")
                            ->danger()
                            ->send();
                        return;
                    }

                    // Nếu không có bản ghi nào dùng, cho phép xóa
                    $record->delete();

                    Notification::make()
                        ->title('Xóa thành công')
                        ->success()
                        ->send();

                    // Quay về trang danh sách
                    redirect()->route('filament.admin.resources.tuy-chon-ket-quas.index');
                }),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Cập nhật Tùy chọn Kết quả thành công')
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
