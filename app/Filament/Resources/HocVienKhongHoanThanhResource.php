<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HocVienKhongHoanThanhResource\Pages;
use App\Models\HocVienKhongHoanThanh;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HocVienKhongHoanThanhResource extends Resource
{
    protected static ?string $model = HocVienKhongHoanThanh::class;

    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationGroup = 'Học viên';
    protected static ?string $navigationLabel = 'Học viên không hoàn thành';
    protected static ?string $modelLabel = 'Học viên không hoàn thành';
    protected static ?string $pluralModelLabel = 'Các học viên không hoàn thành';

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
                Forms\Components\Textarea::make('ly_do_khong_hoan_thanh')
                    ->label('Lý do không hoàn thành')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('co_the_ghi_danh_lai')
                    ->label('Có thể ghi danh lại')
                    ->default(true),
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
                Tables\Columns\TextColumn::make('ly_do_khong_hoan_thanh')
                    ->label('Lý do')
                    ->wrap()
                    ->limit(50),
                Tables\Columns\IconColumn::make('co_the_ghi_danh_lai')
                    ->label('Có thể ghi danh lại')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('khoa_hoc_id')
                    ->label('Khóa học')
                    ->relationship('khoaHoc', 'ma_khoa_hoc'),
                Tables\Filters\SelectFilter::make('co_the_ghi_danh_lai')
                    ->label('Có thể ghi danh lại')
                    ->options([
                        '1' => 'Có',
                        '0' => 'Không',
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
            'index' => Pages\ListHocVienKhongHoanThanhs::route('/'),
            'create' => Pages\CreateHocVienKhongHoanThanh::route('/create'),
            'view' => Pages\ViewHocVienKhongHoanThanh::route('/{record}'),
            'edit' => Pages\EditHocVienKhongHoanThanh::route('/{record}/edit'),
        ];
    }
}
