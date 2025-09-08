<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DangKyResource\Pages;
use App\Models\DangKy;
use App\Models\KhoaHoc;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class DangKyResource extends Resource
{
    protected static ?string $model = DangKy::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $modelLabel = 'Ghi danh';
    protected static ?string $pluralModelLabel = 'Ghi danh';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('hoc_vien_id')
                ->relationship('hocVien','ho_ten')->searchable()->preload()->required()->label('Học viên'),
            Forms\Components\Select::make('khoa_hoc_id')
                ->label('Khóa học')
                ->options(fn() => KhoaHoc::query()->choGhiDanh()->get()->pluck('ten_khoa_hoc','id'))
                ->searchable()->required(),
            Forms\Components\DatePicker::make('ngay_dang_ky')->native(false)->displayFormat('d/m/Y')->default(now())->label('Ngày đăng ký'),
            Forms\Components\Select::make('trang_thai')->options([
                'pending'  => 'Chờ duyệt',
                'approved' => 'Đã duyệt',
                'rejected' => 'Từ chối',
            ])->default('pending'),
        ])->columns(2);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('hocVien.ho_ten')->label('Học viên')->searchable(),
            Tables\Columns\TextColumn::make('khoaHoc.ma_khoa_hoc')->label('Mã KH')->searchable(),
            Tables\Columns\TextColumn::make('khoaHoc.chuongTrinh.ten_chuong_trinh')->label('Chương trình'),
            Tables\Columns\TextColumn::make('ngay_dang_ky')->date('d/m/Y')->label('Ngày ĐK'),
            Tables\Columns\BadgeColumn::make('trang_thai')->colors([
                'warning' => 'pending',
                'success' => 'approved',
                'danger'  => 'rejected',
            ])->label('Trạng thái'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDangKies::route('/'),
            'create' => Pages\CreateDangKy::route('/create'),
            'edit'   => Pages\EditDangKy::route('/{record}/edit'),
        ];
    }
}
