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
    protected static ?string $pluralModelLabel = 'Giảng viên';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\FileUpload::make('hinh_anh_path')
                            ->label('Hình ảnh')
                            ->image()
                            ->imageEditor()
                            ->directory('giangvien')
                            ->columnSpan(1),

                        Forms\Components\Card::make()
                            ->schema([
                                Forms\Components\TextInput::make('ma_so')
                                    ->label('Mã số')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('ho_ten')
                                    ->label('Họ và tên')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('gioi_tinh')
                                    ->label('Giới tính')
                                    ->options(['Nam' => 'Nam', 'Nữ' => 'Nữ'])
                                    ->searchable(),
                                Forms\Components\DatePicker::make('nam_sinh')
                                    ->label('Năm sinh')
                                    ->displayFormat('d/m/Y'),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email (Đăng nhập)')
                                    ->email()
                                    ->maxLength(255)
                                    ->required(fn ($get) => in_array($get('tinh_trang'), ['Đang làm việc', 'Đang giảng dạy'])),
                                Forms\Components\TextInput::make('dien_thoai')
                                    ->label('Số điện thoại')
                                    ->tel()
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('ho_khau_noi_lam_viec')
                                    ->label('Hộ khẩu / Nơi làm việc')
                                    ->maxLength(255),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Thông tin chuyên môn')
                    ->schema([
                        Forms\Components\TextInput::make('don_vi')
                            ->label('Đơn vị làm việc')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('trinh_do')
                            ->label('Trình độ')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('chuyen_mon')
                            ->label('Chuyên môn')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('so_nam_kinh_nghiem')
                            ->label('Số năm kinh nghiệm')
                            ->numeric(),
                        Forms\Components\Textarea::make('tom_tat_kinh_nghiem')
                            ->label('Tóm tắt kinh nghiệm')
                            ->rows(5)
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
                    ])
                    ->columns(2)
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('TT')
                    ->rowIndex(),
                Tables\Columns\ImageColumn::make('hinh_anh_path')
                    ->label('Hình ảnh')
                    ->circular(),
                Tables\Columns\TextColumn::make('ma_so')
                    ->label('Mã số')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ho_ten')
                    ->label('Họ tên')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gioi_tinh')
                    ->label('Giới tính'),
                Tables\Columns\TextColumn::make('nam_sinh')
                    ->label('Năm sinh')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('don_vi')
                    ->label('Đơn vị'),
                Tables\Columns\TextColumn::make('trinh_do_chuyen_mon')
                    ->label('Trình độ chuyên môn')
                    ->getStateUsing(fn ($record) => 
                        ($record->trinh_do ? $record->trinh_do : '') .
                        (($record->trinh_do && $record->chuyen_mon) ? ' - ' : '') .
                        ($record->chuyen_mon ?? '')
                    ),
                Tables\Columns\TextColumn::make('tinh_trang')
                    ->label('Tình trạng')
                    ->badge(),
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
            'index' => Pages\ListGiangViens::route('/'),
            'create' => Pages\CreateGiangVien::route('/create'),
            'edit' => Pages\EditGiangVien::route('/{record}/edit'),
        ];
    }
}
