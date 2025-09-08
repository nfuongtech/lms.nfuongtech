<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DangKyResource\Pages;
use App\Models\DangKy;
use App\Models\HocVien;
use App\Models\KhoaHoc;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DangKyResource extends Resource
{
    /**
     * Model liên kết
     */
    protected static ?string $model = DangKy::class;

    /**
     * Phải có kiểu bool để tương thích với lớp cha Filament\Resources\Resource
     * false => ẩn resource khỏi navigation (theo yêu cầu trước)
     * true  => hiển thị trên navigation
     */
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationLabel = 'Ghi danh';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('hoc_vien_id')
                ->label('Học viên')
                ->searchable()
                ->options(
                    HocVien::where('tinh_trang','Đang làm việc')
                        ->get()
                        ->mapWithKeys(fn($hv) => [$hv->id => "{$hv->msnv} - {$hv->ho_ten}"])
                        ->toArray()
                )
                ->required(),

            Forms\Components\Select::make('khoa_hoc_id')
                ->label('Khóa học')
                ->searchable()
                ->options(
                    KhoaHoc::all()
                        ->mapWithKeys(fn($kh) => [$kh->id => "{$kh->ma_khoa_hoc} - {$kh->ten_khoa_hoc}"])
                        ->toArray()
                )
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hocVien.msnv')->label('MSNV')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('hocVien.ho_ten')->label('Họ tên')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('khoaHoc.ma_khoa_hoc')->label('Mã khóa học')->sortable(),
                Tables\Columns\TextColumn::make('khoaHoc.ten_khoa_hoc')->label('Tên khóa học'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày ghi danh')->dateTime('d/m/Y H:i'),
            ])
            ->filters([])
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
            'index' => Pages\ListDangKies::route('/'),
            'create' => Pages\CreateDangKy::route('/create'),
            'edit' => Pages\EditDangKy::route('/{record}/edit'),
        ];
    }
}
