<?php

namespace App\Filament\Resources\KhoaHocResource\Pages;

use App\Filament\Resources\KhoaHocResource;
use App\Models\ChuongTrinh;
use App\Models\QuyTacMaKhoa;
use Filament\Resources\Pages\CreateRecord;

class CreateKhoaHoc extends CreateRecord
{
    protected static string $resource = KhoaHocResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // % giờ học: số nguyên dương
        if (isset($data['yeu_cau_phan_tram_gio'])) {
            $data['yeu_cau_phan_tram_gio'] = max(1, (int) $data['yeu_cau_phan_tram_gio']);
        }
        // Điểm TB: 1 số lẻ (chấp nhận dấu phẩy)
        if (isset($data['yeu_cau_diem_tb'])) {
            $val = str_replace(',', '.', (string) $data['yeu_cau_diem_tb']);
            $data['yeu_cau_diem_tb'] = round((float) $val, 1);
        }

        // Điền tên khóa học nếu để trống
        if (empty($data['ten_khoa_hoc']) && !empty($data['chuong_trinh_id'])) {
            $ct = ChuongTrinh::find($data['chuong_trinh_id']);
            if ($ct?->ten_chuong_trinh) $data['ten_khoa_hoc'] = $ct->ten_chuong_trinh;
        }

        // Auto mã khóa (điền sẵn để thấy và có thể chỉnh)
        if (($data['che_do_ma_khoa'] ?? 'auto') === 'auto') {
            $ct = isset($data['chuong_trinh_id']) ? ChuongTrinh::find($data['chuong_trinh_id']) : null;
            if ($ct?->loai_hinh_dao_tao) {
                $data['ma_khoa_hoc'] = QuyTacMaKhoa::taoMaKhoaHoc($ct->loai_hinh_dao_tao);
            }
        }
        unset($data['che_do_ma_khoa']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Tạo xong → Edit (mặc định edit_mode=false → khóa “Thông tin chung”)
        return static::getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
