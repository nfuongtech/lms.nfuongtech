<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HocVienHoanThanhResource\Pages;
use App\Models\HocVienHoanThanh;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HocVienHoanThanhResource extends Resource
{
    protected static ?string $model = HocVienHoanThanh::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Học viên';
    protected static ?string $navigationLabel = 'Học viên hoàn thành';
    protected static ?string $modelLabel = 'Học viên hoàn thành';
    protected static ?string $pluralModelLabel = 'Các học viên hoàn thành';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('hoc_vien_id')
                    ->label('Học viên')
                    ->relationship('hocVien', 'ho_ten')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('khoa_hoc_id')
                    ->label('Khóa học')
                    ->relationship('khoaHoc', 'ma_khoa_hoc')
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('ngay_hoan_thanh')
                    ->label('Ngày hoàn thành'),
                Forms\Components\Toggle::make('chung_chi_da_cap')
                    ->label('Chứng chỉ đã cấp')
                    ->default(false),
                Forms\Components\Textarea::make('ghi_chu')
                    ->label('Ghi chú')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hocVien.msnv')
                    ->label('MSNV')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hocVien.ho_ten')
                    ->label('Họ tên')
                    ->sortable(),
                Tables\Columns\TextColumn::make('khoaHoc.ma_khoa_hoc')
                    ->label('Khóa học')
                    ->sortable(),
                Tables\Columns\TextColumn::make('khoaHoc.chuongTrinh.ten_chuong_trinh')
                    ->label('Chương trình')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ngay_hoan_thanh')
                    ->label('Ngày hoàn thành')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('chung_chi_da_cap')
                    ->label('Chứng chỉ')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('khoa_hoc_id')
                    ->label('Khóa học')
                    ->relationship('khoaHoc', 'ma_khoa_hoc'),
                Tables\Filters\SelectFilter::make('chung_chi_da_cap')
                    ->label('Chứng chỉ đã cấp')
                    ->options([
                        '1' => 'Đã cấp',
                        '0' => 'Chưa cấp',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHocVienHoanThanhs::route('/'),
            'create' => Pages\CreateHocVienHoanThanh::route('/create'),
            'view' => Pages\ViewHocVienHoanThanh::route('/{record}'),
            'edit' => Pages\EditHocVienHoanThanh::route('/{record}/edit'),
        ];
    }
}
