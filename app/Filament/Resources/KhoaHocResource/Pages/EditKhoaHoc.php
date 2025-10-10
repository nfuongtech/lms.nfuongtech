<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use App\Models\ChuongTrinh;
use App\Models\QuyTacMaKhoa;
use Filament\Resources\Pages\EditRecord;

class EditKhoaHoc extends EditRecord
{
    protected static string $resource = KhoaHocResource::class;

    public function getTitle(): string
    {
        return 'Sửa Kế hoạch đào tạo';
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
