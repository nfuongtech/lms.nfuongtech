<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HocVienResource\Pages;
use App\Models\DonVi;
use App\Models\HocVien;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HocVienResource extends Resource
{
    protected static ?string $model = HocVien::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $modelLabel = 'Học viên';
    protected static ?string $pluralModelLabel = 'Học viên';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin cá nhân')
                    ->description('Các thông tin cơ bản của học viên.')
                    ->schema([
                        Forms\Components\FileUpload::make('hinh_anh_path')
                            ->label('Hình ảnh 3x4')
                            ->image()
                            ->directory('hoc-vien-images'),
                        Forms\Components\TextInput::make('msnv')
                            ->label('MSNV')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('ho_ten')
                            ->label('Họ và tên')
                            ->required(),
                        Forms\Components\Select::make('gioi_tinh')
                            ->label('Giới tính')
                            ->options(['Nam' => 'Nam', 'Nữ' => 'Nữ', 'Khác' => 'Khác']),
                        Forms\Components\DatePicker::make('nam_sinh')
                            ->label('Năm sinh')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\TextInput::make('sdt')
                            ->label('Số điện thoại')
                            ->tel(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true),
                    ])->columns(2),

                Forms\Components\Section::make('Thông tin công việc')
                    ->schema([
                        Forms\Components\DatePicker::make('ngay_vao')
                            ->label('Ngày vào')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\TextInput::make('chuc_vu')
                            ->label('Chức vụ'),
                        Forms\Components\Select::make('don_vi_id')
                            ->label('Đơn vị (Tên hiển thị)')
                            ->relationship(name: 'donVi', titleAttribute: 'thaco_tdtv')
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => $record->ten_hien_thi)
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('thaco_tdtv')->label('THACO/TĐTV')->required(),
                                Forms\Components\TextInput::make('cong_ty_ban_nvqt')->label('Công ty/Ban NVQT'),
                                Forms\Components\TextInput::make('phong_bo_phan')->label('Phòng/Bộ phận'),
                                Forms\Components\TextInput::make('noi_lam_viec_chi_tiet')->label('Nơi làm việc (Xã, Tỉnh/TP)'),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $today = now()->format('Ymd');
                                $lastRecord = DonVi::where('ma_don_vi', 'like', "{$today}-%")->latest('ma_don_vi')->first();
                                if ($lastRecord) {
                                    $lastNumber = (int) substr($lastRecord->ma_don_vi, -3);
                                    $newNumber = $lastNumber + 1;
                                } else {
                                    $newNumber = 1;
                                }
                                $data['ma_don_vi'] = $today . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

                                return DonVi::create($data)->id;
                            }),
                        Forms\Components\Select::make('don_vi_phap_nhan_id')
                            ->label('Đơn vị trả lương')
                            ->relationship('donViPhapNhan', 'ten_don_vi')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('tinh_trang')
                            ->label('Tình trạng')
                            ->options([
                                'Đang làm việc' => 'Đang làm việc',
                                'Đã nghỉ việc' => 'Đã nghỉ việc',
                                'Nghỉ thai sản' => 'Nghỉ thai sản',
                            ])->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('hinh_anh_path')->label('Hình ảnh')->circular(),
                Tables\Columns\TextColumn::make('msnv')->label('MSNV')->searchable(),
                Tables\Columns\TextColumn::make('ho_ten')->label('Họ và tên')->searchable(),
                Tables\Columns\TextColumn::make('chuc_vu')->label('Chức vụ'),
                Tables\Columns\TextColumn::make('donVi.thaco_tdtv')->label('THACO/TĐTV'),
                Tables\Columns\TextColumn::make('sdt')->label('Số điện thoại'),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('tinh_trang')->label('Tình trạng'),
            ])
            ->filters([
                // ===== BỘ LỌC ĐÃ ĐƯỢỢC NÂNG CẤP GIAO DIỆN =====
                Tables\Filters\Filter::make('loc_thong_tin_hoc_vien')
                    ->form([
                        Forms\Components\Section::make() // Bọc trong Section
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('msnv')
                                            ->placeholder('Nhập MSNV...')
                                            ->prefixIcon('heroicon-o-identification'), // Thêm icon
                                        Forms\Components\TextInput::make('ho_ten')
                                            ->placeholder('Nhập họ và tên...')
                                            ->prefixIcon('heroicon-o-user'),
                                        Forms\Components\TextInput::make('thaco_tdtv')
                                            ->placeholder('Nhập THACO/TĐTV...')
                                            ->prefixIcon('heroicon-o-building-office-2'),
                                        Forms\Components\TextInput::make('noi_lam_viec')
                                            ->placeholder('Nhập nơi làm việc...')
                                            ->prefixIcon('heroicon-o-map-pin'),
                                    ])
                            ])->compact(), // Dùng layout compact cho gọn
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['msnv'], fn(Builder $query, $search): Builder => $query->where('msnv', 'like', "%{$search}%"))
                            ->when($data['ho_ten'], fn(Builder $query, $search): Builder => $query->where('ho_ten', 'like', "%{$search}%"))
                            ->when($data['thaco_tdtv'], fn(Builder $query, $search): Builder => $query->whereHas('donVi', fn($q) => $q->where('thaco_tdtv', 'like', "%{$search}%")))
                            ->when($data['noi_lam_viec'], fn(Builder $query, $search): Builder => $query->whereHas('donVi', fn($q) => $q->where('noi_lam_viec_chi_tiet', 'like', "%{$search}%")));
                    })
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
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
            'index' => Pages\ListHocViens::route('/'),
            'create' => Pages\CreateHocVien::route('/create'),
            'edit' => Pages\EditHocVien::route('/{record}/edit'),
        ];
    }
}
