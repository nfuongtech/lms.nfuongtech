<?php

namespace App\Filament\Imports;

use App\Models\GiangVien;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class GiangVienImporter extends Importer
{
    protected static ?string $model = GiangVien::class;

    public static function getColumns(): array
    {
        return [
            \Filament\Actions\Imports\Columns\ImportColumn::make('ma_so')->requiredMapping()->rules(['required', 'max:255', 'unique:giang_viens,ma_so']),
            \Filament\Actions\Imports\Columns\ImportColumn::make('ho_ten')->requiredMapping()->rules(['required', 'max:255']),
            // Thêm các cột khác tương tự nếu bạn muốn import đầy đủ
        ];
    }

    public function resolveRecord(): ?GiangVien
    {
        return GiangVien::firstOrNew([
            'ma_so' => $this->data['ma_so'],
        ]);
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
