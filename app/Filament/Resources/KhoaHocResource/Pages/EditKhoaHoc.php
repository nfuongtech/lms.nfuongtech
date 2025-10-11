<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use App\Models\ChuongTrinh;
use App\Models\QuyTacMaKhoa;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKhoaHoc extends EditRecord
{
    protected static string $resource = KhoaHocResource::class;

    public function getTitle(): string
    {
        return 'Sửa Kế hoạch đào tạo';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Lưu thay đổi')
                ->color('success'),
            Actions\Action::make('togglePause')
                ->label(fn () => ($this->record->tam_hoan ?? false) ? 'Hủy tạm hoãn' : 'Tạm hoãn')
                ->color(fn () => ($this->record->tam_hoan ?? false) ? 'info' : 'primary')
                ->form(function () {
                    if ($this->record->tam_hoan ?? false) {
                        return [];
                    }

                    return [
                        Textarea::make('ly_do_tam_hoan')
                            ->label('Lý do tạm hoãn')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ];
                })
                ->modalHeading(fn () => ($this->record->tam_hoan ?? false) ? 'Hủy tạm hoãn khóa học' : 'Tạm hoãn khóa học')
                ->modalSubmitActionLabel(fn () => ($this->record->tam_hoan ?? false) ? 'Xác nhận' : 'Tạm hoãn')
                ->requiresConfirmation(fn () => (bool) ($this->record->tam_hoan ?? false))
                ->action(function (array $data) {
                    $record = $this->record;

                    if (!$record) {
                        return;
                    }

                    $isPausing = ! (bool) ($record->tam_hoan ?? false);

                    $record->tam_hoan = $isPausing;
                    $record->ly_do_tam_hoan = $isPausing
                        ? ($data['ly_do_tam_hoan'] ?? null)
                        : null;
                    $record->trang_thai = $isPausing
                        ? 'Tạm hoãn'
                        : $record->calculateScheduleStatus();
                    $record->save();

                    $message = $record->tam_hoan
                        ? 'Khóa học đã được tạm hoãn.'
                        : 'Đã hủy tạm hoãn khóa học.';

                    Notification::make()
                        ->title($message)
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $record]));
                }),
            Actions\Action::make('delete')
                ->label('Xóa')
                ->color('danger')
                ->modalHeading('Xóa Kế hoạch đào tạo')
                ->modalSubmitActionLabel('Xóa')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->record;

                    if ($record) {
                        $record->delete();
                    }

                    Notification::make()
                        ->title('Đã xóa Kế hoạch đào tạo.')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                }),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['yeu_cau_phan_tram_gio'])) {
            $data['yeu_cau_phan_tram_gio'] = max(1, (int) $data['yeu_cau_phan_tram_gio']);
        }
        if (isset($data['yeu_cau_diem_tb'])) {
            $val = str_replace(',', '.', (string) $data['yeu_cau_diem_tb']);
            $data['yeu_cau_diem_tb'] = round((float) $val, 1);
        }
        if (($data['che_do_ma_khoa'] ?? null) === 'auto') {
            $ct = isset($data['chuong_trinh_id']) ? ChuongTrinh::find($data['chuong_trinh_id']) : $this->record->chuongTrinh;
            if ($ct?->loai_hinh_dao_tao) {
                $data['ma_khoa_hoc'] = $data['ma_khoa_hoc'] ?: QuyTacMaKhoa::taoMaKhoaHoc($ct->loai_hinh_dao_tao);
            }
        }
        unset($data['che_do_ma_khoa']);
        return $data;
    }

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
    }
}
