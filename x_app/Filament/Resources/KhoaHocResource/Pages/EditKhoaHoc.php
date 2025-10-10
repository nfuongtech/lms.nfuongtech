<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKhoaHoc extends EditRecord
{
    protected static string $resource = KhoaHocResource::class;

    public function getTitle(): string
    {
        return 'Sửa kế hoạch đào tạo';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Xóa')
                ->modalHeading('Xóa Kế hoạch đào tạo')
                ->modalSubheading('Bạn chắc chắn xóa Kế hoạch này, việc xóa sẽ không phục hồi lại được?')
                ->modalSubmitActionLabel('Xóa')
                ->successNotificationTitle('Đã xóa Kế hoạch đào tạo'),
        ];
    }
}
