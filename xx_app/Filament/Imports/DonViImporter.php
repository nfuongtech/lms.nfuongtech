<?php

namespace App\Filament\Imports;

use App\Models\DonVi;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class DonViImporter extends Importer
{
    protected static ?string $model = DonVi::class;

    public static function getColumns(): array
    {
        return [
            \Filament\Actions\Imports\Columns\ImportColumn::make('ma_don_vi')
                ->requiredMapping()
                ->rules(['required', 'max:255', 'unique:don_vis,ma_don_vi']),
            \Filament\Actions\Imports\Columns\ImportColumn::make('tap_doan_don_vi')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            \Filament\Actions\Imports\Columns\ImportColumn::make('ban_nghiep_vu')
                ->rules(['nullable', 'max:255']),
            \Filament\Actions\Imports\Columns\ImportColumn::make('bo_phan_phong')
                ->rules(['nullable', 'max:255']),
            \Filament\Actions\Imports\Columns\ImportColumn::make('noi_lam_viec')
                ->rules(['nullable', 'max:255']),
        ];
    }

    public function resolveRecord(): ?DonVi
    {
        // Cố gắng tìm đơn vị đã tồn tại bằng mã đơn vị để tránh trùng lặp
        return DonVi::firstOrNew([
            'ma_don_vi' => $this->data['ma_don_vi'],
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

