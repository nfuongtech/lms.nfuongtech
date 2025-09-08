<?php
namespace App\Filament\Imports;
use App\Models\DangKy;
use App\Models\HocVien;
use App\Models\KhoaHoc;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Actions\Imports\Columns\ImportColumn;

class DangKyImporter extends Importer
{
    protected static ?string $model = DangKy::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('ma_khoa_hoc')->label('Mã Khóa học')->requiredMapping()->rules(['required']),
            ImportColumn::make('msnv')->label('MSNV')->requiredMapping()->rules(['required']),
        ];
    }

    public function resolveRecord(): ?DangKy
    {
        $khoaHoc = KhoaHoc::where('ma_khoa_hoc', $this->data['ma_khoa_hoc'])->first();
        $hocVien = HocVien::where('msnv', $this->data['msnv'])->first();
        if (!$khoaHoc || !$hocVien) { return null; }
        return DangKy::firstOrNew(['khoa_hoc_id' => $khoaHoc->id, 'hoc_vien_id' => $hocVien->id]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Đã import thành công ' . number_format($import->successful_rows) . ' dòng.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' dòng bị lỗi.';
        }
        return $body;
    }
}
