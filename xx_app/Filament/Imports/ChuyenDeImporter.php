<?php

namespace App\Filament\Imports;

use App\Models\ChuyenDe;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ChuyenDeImporter extends Importer
{
    protected static ?string $model = ChuyenDe::class;

    public static function getColumns(): array
    {
        return [
            \Filament\Actions\Imports\Columns\ImportColumn::make('ma_so')
                ->requiredMapping()
                ->rules(['required', 'max:255', 'unique:chuyen_des,ma_so']),
            \Filament\Actions\Imports\Columns\ImportColumn::make('ten_chuyen_de')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            \Filament\Actions\Imports\Columns\ImportColumn::make('thoi_luong')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            \Filament\Actions\Imports\Columns\ImportColumn::make('doi_tuong_dao_tao')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            \Filament\Actions\Imports\Columns\ImportColumn::make('muc_tieu')
                ->rules(['nullable']),
            \Filament\Actions\Imports\Columns\ImportColumn::make('noi_dung')
                ->rules(['nullable']),
        ];
    }

    public function resolveRecord(): ?ChuyenDe
    {
        return ChuyenDe::firstOrNew([
            // Cố gắng tìm chuyên đề đã tồn tại bằng mã số để tránh trùng lặp
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
