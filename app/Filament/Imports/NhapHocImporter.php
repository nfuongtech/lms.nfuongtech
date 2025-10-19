<?php

namespace App\Filament\Imports;

use App\Models\ChuyenDe;
use App\Models\DonVi;
use App\Models\GiangVien;
use App\Models\NhapHoc;
use Carbon\Carbon;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Actions\Imports\Columns\ImportColumn; // DÒNG QUAN TRỌNG ĐÃ ĐƯỢC THÊM VÀO

class NhapHocImporter extends Importer
{
    protected static ?string $model = NhapHoc::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('msnv')->requiredMapping()->rules(['required']),
            ImportColumn::make('ho_ten')->requiredMapping()->rules(['required']),
            ImportColumn::make('gioi_tinh')->rules(['nullable']),
            ImportColumn::make('nam_sinh')->rules(['nullable', 'date_format:d/m/Y']),
            ImportColumn::make('ngay_vao')->rules(['nullable', 'date_format:d/m/Y']),
            ImportColumn::make('chuc_vu')->rules(['nullable']),
            ImportColumn::make('tap_doan_don_vi')->label('Tập đoàn/Đơn vị')->rules(['nullable']),
            ImportColumn::make('ten_chuyen_de')->label('Tên chuyên đề')->rules(['nullable']),
            ImportColumn::make('lop_khoa')->rules(['nullable']),
            ImportColumn::make('email')->rules(['nullable', 'email']),
            ImportColumn::make('gio_dao_tao')->rules(['nullable', 'date_format:H:i']),
            ImportColumn::make('ngay_dao_tao')->rules(['nullable', 'date_format:d/m/Y']),
            ImportColumn::make('ten_giang_vien')->label('Tên giảng viên')->rules(['nullable']),
        ];
    }

    public function resolveRecord(): ?NhapHoc
    {
        $nhapHoc = new NhapHoc();

        if (! empty($this->data['tap_doan_don_vi'])) {
            $donVi = DonVi::firstOrCreate(
                ['tap_doan_don_vi' => $this->data['tap_doan_don_vi']],
                ['ma_don_vi' => 'DV-' . uniqid()]
            );
            $nhapHoc->don_vi_id = $donVi->id;
        }

        if (! empty($this->data['ten_chuyen_de'])) {
            $chuyenDe = ChuyenDe::where('ten_chuyen_de', $this->data['ten_chuyen_de'])->first();
            if ($chuyenDe) {
                $nhapHoc->chuyen_de_id = $chuyenDe->id;
            }
        }

        if (! empty($this->data['ten_giang_vien'])) {
            $giangVien = GiangVien::where('ho_ten', $this->data['ten_giang_vien'])->first();
            if ($giangVien) {
                $nhapHoc->giang_vien_id = $giangVien->id;
            }
        }

        if (!empty($this->data['nam_sinh'])) {
            $this->data['nam_sinh'] = Carbon::createFromFormat('d/m/Y', $this->data['nam_sinh'])->format('Y-m-d');
        }
        if (!empty($this->data['ngay_vao'])) {
            $this->data['ngay_vao'] = Carbon::createFromFormat('d/m/Y', $this->data['ngay_vao'])->format('Y-m-d');
        }
        if (!empty($this->data['ngay_dao_tao'])) {
            $this->data['ngay_dao_tao'] = Carbon::createFromFormat('d/m/Y', $this->data['ngay_dao_tao'])->format('Y-m-d');
        }

        return $nhapHoc;
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
