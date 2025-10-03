<?php

namespace App\Filament\Resources;

use App\Models\HocVienKhongHoanThanh;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HocVienKhongHoanThanhResource extends Resource
{
    protected static ?string $model = HocVienKhongHoanThanh::class;
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationLabel = 'Học viên không hoàn thành';

    public static function getSlug(): string
    {
        return 'hoc-vien-khong-hoan-thanhs';
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
                Tables\Columns\TextColumn::make('ly_do_khong_hoan_thanh')->label('Lý do không hoàn thành')->wrap()->toggleable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => HocVienKhongHoanThanhResource\Pages\ListHocVienKhongHoanThanhs::route('/'),
        ];
    }
}
