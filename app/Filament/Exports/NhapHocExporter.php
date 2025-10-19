<?php

namespace App\Filament\Exports;

use App\Models\NhapHoc;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class NhapHocExporter extends Exporter
{
    protected static ?string $model = NhapHoc::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('msnv')->label('MSNV'),
            ExportColumn::make('ho_ten')->label('Họ và tên'),
            ExportColumn::make('gioi_tinh')->label('Giới tính'),
            ExportColumn::make('nam_sinh')->label('Năm sinh')->state(fn (NhapHoc $record) => $record->nam_sinh ? $record->nam_sinh->format('d/m/Y') : ''),
            ExportColumn::make('ngay_vao')->label('Ngày vào')->state(fn (NhapHoc $record) => $record->ngay_vao ? $record->ngay_vao->format('d/m/Y') : ''),
            ExportColumn::make('chuc_vu')->label('Chức vụ'),
            ExportColumn::make('donVi.bo_phan_phong')->label('Bộ phận/Phòng'),
            ExportColumn::make('donVi.ban_nghiep_vu')->label('Ban/Công ty'),
            ExportColumn::make('donVi.tap_doan_don_vi')->label('THACO/Tập đoàn'),
            ExportColumn::make('chuyenDe.ten_chuyen_de')->label('Tham dự chuyên đề'),
            ExportColumn::make('lop_khoa')->label('Lớp/Khóa'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('gio_dao_tao')->label('Giờ đào tạo'),
            ExportColumn::make('ngay_dao_tao')->label('Ngày đào tạo')->state(fn (NhapHoc $record) => $record->ngay_dao_tao ? $record->ngay_dao_tao->format('d/m/Y') : ''),
            ExportColumn::make('giangVien.ho_ten')->label('Giảng viên'),
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
