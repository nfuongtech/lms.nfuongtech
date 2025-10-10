<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Filament\Forms;

class EditKhoaHoc extends EditRecord
{
    protected static string $resource = KhoaHocResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Chặn tuyệt đối việc ghi che_do_ma_khoa xuống DB
        unset($data['che_do_ma_khoa']);
        return $data;
    }

    protected function getHeaderActions(): array
    {
        $tamHoan = Actions\Action::make('tam_hoan')
            ->label('Tạm hoãn')
            ->color('danger')
            ->outlined()
            ->visible(fn () => ! (bool) ($this->record->tam_hoan ?? false))
            ->form([
                Forms\Components\Textarea::make('ly_do_tam_hoan')
                    ->label('Lý do tạm hoãn (có thể bỏ qua)')
                    ->rows(3),
            ])
            ->modalHeading('Xác nhận tạm hoãn kế hoạch')
            ->modalSubmitActionLabel('Xác nhận')
            ->modalCancelActionLabel('Hủy')
            ->action(function (array $data) {
                $this->record->tam_hoan = true;
                $this->record->ly_do_tam_hoan = $data['ly_do_tam_hoan'] ?? null;
                $this->record->save();
                $this->record->syncTrangThai();

                Notification::make()->title('Đã tạm hoãn kế hoạch.')->success()->send();
                $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
            });

        $boTamHoan = Actions\Action::make('bo_tam_hoan')
            ->label('Bỏ tạm hoãn')
            ->color('success')
            ->outlined()
            ->visible(fn () => (bool) ($this->record->tam_hoan ?? false))
            ->requiresConfirmation()
            ->modalHeading('Bỏ tạm hoãn kế hoạch')
            ->modalSubmitActionLabel('Xác nhận')
            ->modalCancelActionLabel('Hủy')
            ->action(function () {
                $this->record->tam_hoan = false;
                $this->record->ly_do_tam_hoan = null;
                $this->record->save();
                $this->record->syncTrangThai();

                Notification::make()->title('Đã bỏ tạm hoãn.')->success()->send();
                $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
            });

        return [
            $tamHoan,
            $boTamHoan,
            ...parent::getHeaderActions(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->label('Lưu'),
            $this->getCancelFormAction()->label('Hủy'),
        ];
    }
}
