<?php
namespace App\Filament\Exports;
use App\Models\DangKy;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DangKyExporter extends Exporter
{
    protected static ?string $model = DangKy::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('khoaHoc.ma_khoa_hoc')->label('Mã Khóa học'),
            ExportColumn::make('hocVien.msnv')->label('MSNV'),
            ExportColumn::make('hocVien.ho_ten')->label('Họ và tên'),
            ExportColumn::make('created_at')->label('Ngày Ghi danh'),
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
