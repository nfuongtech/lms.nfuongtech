<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KhoaHocResource\Pages;
use App\Models\KhoaHoc;
use App\Models\GiangVien;
use App\Models\ChuyenDe;
use App\Filament\Resources\TuyChonKetQuaResource;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KhoaHocResource extends Resource
{
    protected static ?string $model = KhoaHoc::class;

    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $modelLabel = 'Khóa học';
    protected static ?string $pluralModelLabel = 'Khóa học';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('ma_khoa_hoc')
                    ->label('Mã khóa học')
                    ->required(),
                TextInput::make('ten_khoa_hoc')
                    ->label('Tên khóa học')
                    ->required(),
                Select::make('giang_vien_id')
                    ->label('Giảng viên')
                    ->options(GiangVien::orderBy('ho_ten')->pluck('ho_ten','id'))
                    ->searchable()
                    ->required(),
                Select::make('chuyen_de_id')
                    ->label('Chuyên đề')
                    ->options(ChuyenDe::orderBy('ten_chuyen_de')->pluck('ten_chuyen_de','id'))
                    ->searchable()
                    ->required(),
                Select::make('trang_thai')
                    ->label('Trạng thái')
                    ->options(fn() => TuyChonKetQuaResource::getTrangThaiOptions())
                    ->required(),
                Repeater::make('lich_hocs')
                    ->label('Lịch học')
                    ->schema([
                        DatePicker::make('ngay_hoc')->label('Ngày học')->required(),
                        TimePicker::make('gio_bat_dau')->label('Giờ bắt đầu')->required(),
                        TimePicker::make('gio_ket_thuc')->label('Giờ kết thúc')->required(),
                        TextInput::make('dia_diem')->label('Địa điểm'),
                        Select::make('giang_vien_id')
                            ->label('Giảng viên')
                            ->options(GiangVien::orderBy('ho_ten')->pluck('ho_ten','id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(5)
                    ->createItemButtonLabel('Thêm lịch học')
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ma_khoa_hoc')->label('Mã khóa học'),
                TextColumn::make('ten_khoa_hoc')->label('Tên khóa học'),
                TextColumn::make('giangVien.ho_ten')->label('Giảng viên'),
                TextColumn::make('chuyenDe.ten_chuyen_de')->label('Chuyên đề'),
                TextColumn::make('trang_thai')->label('Trạng thái'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKhoaHocs::route('/'),
            'create' => Pages\CreateKhoaHoc::route('/create'),
            'edit' => Pages\EditKhoaHoc::route('/{record}/edit'),
        ];
    }
}
