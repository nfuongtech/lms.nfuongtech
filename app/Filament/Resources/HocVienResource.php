<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HocVienResource\Pages;
use App\Models\DonVi;
use App\Models\HocVien;
use App\Models\TuyChonKetQua;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Forms\Components\Grid::make(3)->schema([ // Chia làm 3 cột: 1 ảnh, 2 thông tin
                    // Cột 1: Hình ảnh
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

                    // Cột 2: Thông tin cá nhân (chia 2 cột nhỏ)
                    Forms\Components\Section::make('Thông tin cá nhân')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('msnv')
                                    ->label('MSNV')
                                    ->hint('Có thể để trống để hệ thống tự sinh mã theo HV-YYMMDDXX')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->columnSpan(1), // 20%

                                Forms\Components\TextInput::make('ho_ten')
                                    ->label('Họ và tên')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1), // 80%

                                Forms\Components\Select::make('gioi_tinh')
                                    ->label('Giới tính')
                                    ->options(['Nam' => 'Nam', 'Nữ' => 'Nữ', 'Khác' => 'Khác'])
                                    ->columnSpan(1),

                                Forms\Components\DatePicker::make('nam_sinh')
                                    ->label('Năm sinh')
                                    ->displayFormat('d/m/Y')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email (nhận thông báo)')
                                    // ->email() // Bỏ ràng buộc email để cho phép trống
                                    ->unique(ignoreRecord: true)
                                    ->columnSpan(2), // Không bắt buộc, chiếm toàn bộ dòng
                            ]),
                        ])
                        ->columnSpan(2),
                ]),

                Forms\Components\Section::make('Thông tin công việc')
                    ->schema([
                        Forms\Components\DatePicker::make('ngay_vao')->label('Ngày vào'),
                        Forms\Components\TextInput::make('chuc_vu')->label('Chức vụ'),

                        // Select liên kết DonVi qua quan hệ 'donVi' và hiển thị ten_hien_thi
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
                            ->createOptionUsing(function (array $data): int {
                                // tạo DonVi mới và trả id
                                $donVi = DonVi::create($data);
                                return $donVi->id;
                            }),

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
                Tables\Columns\BadgeColumn::make('tinh_trang')->label('Tình trạng'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('don_vi.thaco_tdtv')
                    ->label('THACO/TĐTV')
                    ->relationship('donVi', 'thaco_tdtv')
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    // --- Bắt đầu: Thêm phương thức lấy thống kê ---
    public static function getThongKeTheoDonVi(): array
    {
        $stats = HocVien::join('don_vis', 'hoc_viens.don_vi_id', '=', 'don_vis.id')
            ->selectRaw('don_vis.thaco_tdtv, don_vis.cong_ty_ban_nvqt, COUNT(hoc_viens.id) as so_luong')
            ->where('hoc_viens.tinh_trang', 'Đang làm việc')
            ->groupBy('don_vis.thaco_tdtv', 'don_vis.cong_ty_ban_nvqt')
            ->orderBy('don_vis.thaco_tdtv')
            ->orderBy('don_vis.cong_ty_ban_nvqt')
            ->get();

        $result = [];
        $stt = 1;
        foreach ($stats as $stat) {
            $result[] = [
                'stt' => $stt++,
                'thaco_tdtv' => $stat->thaco_tdtv,
                'cong_ty_ban_nvqt' => $stat->cong_ty_ban_nvqt,
                'so_luong' => $stat->so_luong,
            ];
        }

        return $result;
    }
    // --- Kết thúc: Thêm phương thức lấy thống kê ---

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHocViens::route('/'),
            'create' => Pages\CreateHocVien::route('/create'),
            'edit' => Pages\EditHocVien::route('/{record}/edit'),
        ];
    }
}
