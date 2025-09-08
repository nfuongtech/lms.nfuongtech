<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GiangVienResource\Pages;
use App\Models\GiangVien;
use App\Models\TuyChonKetQua;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GiangVienResource extends Resource
{
    protected static ?string $model = GiangVien::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $modelLabel = 'Giảng viên';
    protected static ?string $pluralModelLabel = 'Các Giảng viên';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ma_so')
                    ->label('Mã số')
                    ->required(),
                Forms\Components\TextInput::make('ho_ten')
                    ->label('Họ và tên')
                    ->required(),
                Forms\Components\FileUpload::make('hinh_anh_path')
                    ->label('Hình ảnh')
                    ->image(),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                Forms\Components\Select::make('gioi_tinh')
                    ->label('Giới tính')
                    ->options(['Nam' => 'Nam', 'Nữ' => 'Nữ']),
                Forms\Components\DatePicker::make('nam_sinh')
                    ->label('Năm sinh'),
                Forms\Components\TextInput::make('don_vi')
                    ->label('Đơn vị'),
                Forms\Components\TextInput::make('ho_khau_noi_lam_viec')
                    ->label('Hộ khẩu/Nơi làm việc'),
                Forms\Components\TextInput::make('trinh_do')
                    ->label('Trình độ'),
                Forms\Components\TextInput::make('chuyen_mon')
                    ->label('Chuyên môn'),
                Forms\Components\TextInput::make('so_nam_kinh_nghiem')
                    ->label('Số năm kinh nghiệm')
                    ->numeric(),
                Forms\Components\Textarea::make('tom_tat_kinh_nghiem')
                    ->label('Tóm tắt kinh nghiệm')
                    ->columnSpanFull(),
                Forms\Components\Select::make('tinh_trang')
                    ->label('Tình trạng')
                    ->options(fn () => TuyChonKetQua::where('loai', 'tinh_trang_giang_vien')->pluck('gia_tri', 'gia_tri'))
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('gia_tri')
                            ->label('Tên trạng thái mới')
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data): string {
                        $newOption = TuyChonKetQua::create([
                            'loai' => 'tinh_trang_giang_vien',
                            'gia_tri' => $data['gia_tri']
                        ]);
                        return $newOption->gia_tri;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ma_so')
                    ->label('Mã số')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ho_ten')
                    ->label('Họ tên')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('hinh_anh_path')
                    ->label('Hình ảnh'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tinh_trang')
                    ->label('Tình trạng')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListGiangViens::route('/'),
            'create' => Pages\CreateGiangVien::route('/create'),
            'edit' => Pages\EditGiangVien::route('/{record}/edit'),
        ];
    }    
}
