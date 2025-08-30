<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GiangVienResource\Pages;
use App\Models\GiangVien;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class GiangVienResource extends Resource
{
    protected static ?string $model = GiangVien::class;

    protected static ?string $navigationGroup = 'Thiết lập';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $modelLabel = 'Giảng viên';
    protected static ?string $pluralModelLabel = 'Giảng viên';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thông tin Giảng viên')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('hinh_anh_path')->label('Hình ảnh giảng viên')->image()->directory('giang-vien-images')->columnSpanFull(),
                        TextInput::make('ma_so')->label('Mã số')->required()->unique(ignoreRecord: true),
                        TextInput::make('ho_ten')->label('Họ tên')->required(),
                        Select::make('gioi_tinh')->label('Giới tính')->options(['Nam' => 'Nam', 'Nữ' => 'Nữ', 'Khác' => 'Khác']),
                        DatePicker::make('nam_sinh')->label('Năm sinh')->displayFormat('d/m/Y'),
                        TextInput::make('don_vi')->label('Đơn vị (Tự nhập)'),
                        Select::make('chuyenDes')
                            ->label('Dạy chuyên đề')
                            ->relationship('chuyenDes', 'ten_chuyen_de')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        TextInput::make('ho_khau_noi_lam_viec')->label('Hộ khẩu/Nơi làm việc'),
                        TextInput::make('trinh_do')->label('Trình độ'),
                        TextInput::make('chuyen_mon')->label('Chuyên môn'),
                        TextInput::make('so_nam_kinh_nghiem')->label('Số năm kinh nghiệm')->numeric(),
                        Textarea::make('tom_tat_kinh_nghiem')->label('Tóm tắt kinh nghiệm')->columnSpanFull(),
                    ]),
                Section::make('Tài khoản Người dùng (Phân quyền)')
                    ->description('Tạo tài khoản để giảng viên có thể đăng nhập hệ thống.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')->label('Email đăng nhập')->email()->required()->unique(table: User::class, column: 'email', ignoreRecord: true),
                        TextInput::make('password')->label('Mật khẩu')->password()->required()->minLength(8)->dehydrateStateUsing(fn ($state) => Hash::make($state))->dehydrated(fn ($state) => filled($state)),
                        Select::make('role_id')
                            ->label('Vai trò')
                            ->options(Role::pluck('name', 'id'))
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('hinh_anh_path')->label('Hình ảnh')->circular(),
                TextColumn::make('ma_so')->label('Mã số')->searchable(),
                TextColumn::make('ho_ten')->label('Họ tên')->searchable(),
                TextColumn::make('don_vi')->label('Đơn vị'),
                TextColumn::make('chuyen_mon')->label('Chuyên môn'),
                TextColumn::make('user.email')->label('Email tài khoản'),
                TextColumn::make('user.roles.name')->label('Vai trò')->badge(),
            ])
            ->filters([
                //
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
