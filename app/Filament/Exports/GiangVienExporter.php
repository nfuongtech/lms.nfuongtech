<?php

namespace App\Filament\Exports;

use App\Models\GiangVien;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class GiangVienExporter extends Exporter
{
    protected static ?string $model = GiangVien::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('ma_so')->label('Mã số'),
            ExportColumn::make('ho_ten')->label('Họ tên'),
            ExportColumn::make('gioi_tinh')->label('Giới tính'),
            ExportColumn::make('nam_sinh')->label('Năm sinh')->state(fn (GiangVien $record) => $record->nam_sinh ? $record->nam_sinh->format('d/m/Y') : ''),
            ExportColumn::make('donVi.ten_hien_thi')->label('Đơn vị'),
            ExportColumn::make('chuyenDes')->label('Dạy chuyên đề')->state(fn (GiangVien $record) => $record->chuyenDes->pluck('ten_chuyen_de')->implode(', ')),
            ExportColumn::make('ho_khau_noi_lam_viec')->label('Hộ khẩu/Nơi làm việc'),
            ExportColumn::make('trinh_do')->label('Trình độ'),
            ExportColumn::make('chuyen_mon')->label('Chuyên môn'),
            ExportColumn::make('so_nam_kinh_nghiem')->label('Số năm kinh nghiệm'),
            ExportColumn::make('tom_tat_kinh_nghiem')->label('Tóm tắt kinh nghiệm'),
            ExportColumn::make('user.roles.name')->label('Vai trò'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Đã xuất thành công ' . number_format($export->successful_rows) . ' dòng.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' dòng bị lỗi.';
        }
        return $body;
    }
}
