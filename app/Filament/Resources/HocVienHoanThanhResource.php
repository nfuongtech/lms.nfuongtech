<?php

namespace App\Filament\Resources;

use App\Models\HocVienHoanThanh;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HocVienHoanThanhResource extends Resource
{
    protected static ?string $model = HocVienHoanThanh::class;
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'Học viên hoàn thành';

    public static function getSlug(): string
    {
        return 'hoc-vien-hoan-thanhs';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')->label('STT')->rowIndex(),
                Tables\Columns\TextColumn::make('hocVien.msnv')->label('MSNV')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('hocVien.ho_ten')->label('Họ tên')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('khoaHoc.ma_khoa_hoc')->label('Mã khóa')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('ketQua.diem_tong_khoa')->label('Điểm TB khóa')->numeric(2)->sortable(),
                Tables\Columns\TextColumn::make('ngay_hoan_thanh')->label('Ngày hoàn thành')->date()->sortable(),
                Tables\Columns\TextColumn::make('ghi_chu')->label('Ghi chú')->wrap()->toggleable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => HocVienHoanThanhResource\Pages\ListHocVienHoanThanhs::route('/'),
        ];
    }
}
