<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HocVienResource\Pages;
use App\Models\DonVi;
use App\Models\HocVien;
use App\Models\TuyChonKetQua;
use App\Models\DonViPhapNhan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HocVienResource extends Resource
{
    protected static ?string $model = HocVien::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationLabel = 'Học viên';
    protected static ?string $modelLabel = 'Học viên';
    protected static ?string $pluralModelLabel = 'Học viên';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Section::make('Hình ảnh 3x4')
                        ->schema([
                            Forms\Components\FileUpload::make('hinh_anh_path')
                                ->label('')
                                ->image()
                                ->directory('hoc-vien-images')
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('3:4')
                                ->imageResizeTargetWidth('150')
                                ->imageResizeTargetHeight('200')
                                ->alignCenter()
                                ->panelLayout('compact'),
                        ])
                        ->columnSpan(1),

                    Forms\Components\Section::make('Thông tin cá nhân')
                        ->schema([
                            Forms\Components\TextInput::make('msnv')
                                ->label('MSNV')
                                ->hint('Có thể để trống để hệ thống tự sinh mã theo HV-YYMMXXX')
                                ->unique(ignoreRecord: true)
                                ->maxLength(50)
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('ho_ten')
                                ->label('Họ và tên')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('gioi_tinh')
                                    ->label('Giới tính')
                                    ->options(['Nam' => 'Nam', 'Nữ' => 'Nữ', 'Khác' => 'Khác'])
                                    ->columnSpan(1),

                                Forms\Components\DatePicker::make('nam_sinh')
                                    ->label('Năm sinh')
                                    ->displayFormat('d/m/Y')
                                    ->columnSpan(1),
                            ])->columnSpanFull(),

                            // 👉 Số điện thoại + Email
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('sdt')
                                    ->label('Số điện thoại')
                                    ->maxLength(20)
                                    ->placeholder('VD: 0988xxxxxx hoặc +84xxxxxx')
                                    ->rule('regex:/^(\+?\d{6,20})$/')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email (nhận thông báo)')
                                    ->unique(ignoreRecord: true)
                                    ->email()
                                    ->columnSpan(1),
                            ])->columnSpanFull(),

                            Forms\Components\Select::make('don_vi_phap_nhan_id')
                                ->label('Đơn vị pháp nhân / Trả lương')
                                ->relationship('donViPhapNhan', 'ten_don_vi')
                                ->searchable()
                                ->preload()
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(2),
                ]),

                Forms\Components\Section::make('Thông tin công việc')
                    ->schema([
                        Forms\Components\DatePicker::make('ngay_vao')->label('Ngày vào'),
                        Forms\Components\TextInput::make('chuc_vu')->label('Chức vụ'),

                        Forms\Components\Select::make('don_vi_id')
                            ->label('Đơn vị')
                            ->relationship('donVi', 'ten_hien_thi')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('thaco_tdtv')->label('THACO/TĐTV')->required(),
                                Forms\Components\TextInput::make('cong_ty_ban_nvqt')->label('Công ty/Ban NVQT'),
                                Forms\Components\TextInput::make('phong_bo_phan')->label('Phòng/Bộ phận'),
                                Forms\Components\TextInput::make('noi_lam_viec_chi_tiet')->label('Nơi làm việc'),
                            ])
                            ->createOptionUsing(fn (array $data): int => DonVi::create($data)->id),

                        Forms\Components\Select::make('tinh_trang')
                            ->label('Tình trạng')
                            ->options(fn () => TuyChonKetQua::where('loai', 'tinh_trang_hoc_vien')->pluck('gia_tri', 'gia_tri')->toArray())
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('gia_tri')->label('Tên trạng thái')->required(),
                            ])
                            ->createOptionUsing(fn ($data) => TuyChonKetQua::create([
                                'loai' => 'tinh_trang_hoc_vien',
                                'gia_tri' => $data['gia_tri'],
                            ])->gia_tri),
                    ])->columns(2),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['msnv'])) {
            $data['msnv'] = self::generateMSNV();
        }
        if (empty($data['tinh_trang'])) {
            $data['tinh_trang'] = 'Đang làm việc';
        }
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['msnv'])) {
            $data['msnv'] = self::generateMSNV();
        }
        if (empty($data['tinh_trang'])) {
            $data['tinh_trang'] = 'Đang làm việc';
        }
        return $data;
    }

    private static function generateMSNV(): string
    {
        $prefix = 'HV-' . now()->format('ym');
        $fullPrefix = $prefix . '%';
        $last = HocVien::where('msnv', 'like', $fullPrefix)->orderBy('msnv', 'desc')->first();
        if ($last && preg_match('/(\d{3})$/', $last->msnv, $m)) {
            $num = intval($m[1]) + 1;
        } else {
            $num = 1;
        }
        return $prefix . str_pad(min($num, 999), 3, '0', STR_PAD_LEFT);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('hinh_anh_path')->label('Ảnh')->circular()->width(40),
                Tables\Columns\TextColumn::make('msnv')->label('MSNV')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('ho_ten')->label('Họ tên')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('chuc_vu')->label('Chức vụ'),
                Tables\Columns\TextColumn::make('donVi.ten_hien_thi')->label('Đơn vị')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('sdt')->label('SĐT')->searchable(),
                Tables\Columns\BadgeColumn::make('tinh_trang')->label('Tình trạng'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('don_vi_id')
                    ->label('Đơn vị')
                    ->relationship('donVi', 'ten_hien_thi')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('don_vi_phap_nhan_id')
                    ->label('Đơn vị pháp nhân')
                    ->options(fn () => DonViPhapNhan::query()->pluck('ten_don_vi', 'ma_so_thue')->toArray())
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('tinh_trang')
                    ->label('Tình trạng')
                    ->options(fn () => TuyChonKetQua::where('loai', 'tinh_trang_hoc_vien')->pluck('gia_tri', 'gia_tri')->toArray())
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHocViens::route('/'),
            'create' => Pages\CreateHocVien::route('/create'),
            'edit' => Pages\EditHocVien::route('/{record}/edit'),
        ];
    }
}
