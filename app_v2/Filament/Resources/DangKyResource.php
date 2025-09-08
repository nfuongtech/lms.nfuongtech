<?php

namespace App\Filament\Resources;

use App\Enums\TrangThaiKhoaHoc;
use App\Filament\Resources\DangKyResource\Pages;
use App\Models\DangKy;
use App\Models\HocVien;
use App\Models\KhoaHoc;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DangKyResource extends Resource
{
    protected static ?string $model = DangKy::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Quản lý đào tạo';
    protected static ?string $modelLabel = 'Ghi danh';
    protected static ?string $pluralModelLabel = 'Ghi danh';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('hoc_vien_id')
                    ->label('Học viên')
                    ->relationship('hocVien', 'ten_hoc_vien')
                    ->searchable()
                    ->required(),

                Select::make('khoa_hoc_id')
                    ->label('Khóa học')
                    ->relationship(
                        name: 'khoaHoc',
                        titleAttribute: 'ma_khoa_hoc',
                        modifyQueryUsing: fn ($query) => $query->where('trang_thai', TrangThaiKhoaHoc::KE_HOACH)
                    )
                    ->searchable()
                    ->required(),

                DatePicker::make('ngay_dang_ky')
                    ->label('Ngày đăng ký')
                    ->required(),

                Select::make('trang_thai')
                    ->label('Trạng thái')
                    ->options([
                        'dang_cho' => 'Đang chờ',
                        'da_xac_nhan' => 'Đã xác nhận',
                        'huy' => 'Hủy',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hocVien.ten_hoc_vien')->label('Học viên'),
                Tables\Columns\TextColumn::make('khoaHoc.ma_khoa_hoc')->label('Khóa học'),
                Tables\Columns\TextColumn::make('ngay_dang_ky')->date()->label('Ngày đăng ký'),
                Tables\Columns\TextColumn::make('trang_thai')->label('Trạng thái')->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDangKys::route('/'),
            'create' => Pages\CreateDangKy::route('/create'),
            'edit' => Pages\EditDangKy::route('/{record}/edit'),
        ];
    }
}
