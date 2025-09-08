<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KhoaHocResource\Pages;
use App\Filament\Resources\KhoaHocResource\RelationManagers;
use App\Models\ChuongTrinh;
use App\Models\ChuyenDe;
use App\Models\GiangVien;
use App\Models\KhoaHoc;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;

class KhoaHocResource extends Resource
{
    protected static ?string $model = KhoaHoc::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack'; // fix icon
    protected static ?string $navigationGroup = 'Đào tạo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('ten_khoa_hoc')->required()->label('Tên khóa học'),
                    Forms\Components\TextInput::make('ma_khoa_hoc')->label('Mã khóa học'),
                    Forms\Components\Select::make('chuong_trinh_id')
                        ->label('Chương trình')
                        ->options(ChuongTrinh::pluck('ten_chuong_trinh', 'id')->toArray())
                        ->searchable()
                        ->reactive()
                        ->required(),
                    Forms\Components\DatePicker::make('ngay_bat_dau')->label('Ngày bắt đầu'),
                    Forms\Components\DatePicker::make('ngay_ket_thuc')->label('Ngày kết thúc'),
                    Forms\Components\Select::make('trang_thai')
                        ->label('Trạng thái')
                        ->options([
                            'ke_hoach' => 'Kế hoạch',
                            'ban_hanh' => 'Ban hành',
                            'chinh_sua' => 'Chỉnh sửa kế hoạch',
                            'tam_hoan' => 'Tạm hoãn',
                            'dang_dao_tao' => 'Đang đào tạo',
                            'ket_thuc' => 'Kết thúc',
                        ])->default('ke_hoach'),
                    Forms\Components\RichEditor::make('ghi_chu')->label('Ghi chú')->nullable(),
                ]),

                Forms\Components\Section::make('Lịch học')
                    ->schema([
                        Forms\Components\Repeater::make('lichHocs')   // ✅ dùng camelCase
                            ->relationship('lichHocs')              // ✅ chỉ rõ relationship
                            ->label('Lịch học')
                            ->schema([
                                Forms\Components\Select::make('chuyen_de_id')
                                    ->label('Chuyên đề')
                                    ->reactive()
                                    ->options(function (callable $get, $record) {
                                        $chuongTrinhId = $get('chuong_trinh_id') ?? ($record?->chuong_trinh_id ?? null);
                                        if (!$chuongTrinhId) {
                                            return ChuyenDe::pluck('ten_chuyen_de', 'id')->toArray();
                                        }
                                        $ids = DB::table('chuong_trinh_chuyen_de')
                                            ->where('chuong_trinh_id', $chuongTrinhId)
                                            ->pluck('chuyen_de_id')
                                            ->toArray();
                                        return ChuyenDe::whereIn('id', $ids)->pluck('ten_chuyen_de', 'id')->toArray();
                                    })
                                    ->required(),

                                Forms\Components\Select::make('giang_vien_id')
                                    ->label('Giảng viên')
                                    ->options(function (callable $get) {
                                        $chuyenDeId = $get('chuyen_de_id');
                                        if (!$chuyenDeId) {
                                            return GiangVien::pluck('ho_ten', 'id')->toArray();
                                        }
                                        $ids = DB::table('chuyen_de_giang_vien')
                                            ->where('chuyen_de_id', $chuyenDeId)
                                            ->pluck('giang_vien_id')
                                            ->toArray();
                                        return GiangVien::whereIn('id', $ids)->pluck('ho_ten', 'id')->toArray();
                                    })
                                    ->required(),

                                Forms\Components\DatePicker::make('ngay_hoc')->label('Ngày học')->required(),
                                Forms\Components\TimePicker::make('gio_bat_dau')->label('Giờ bắt đầu')->required(),
                                Forms\Components\TimePicker::make('gio_ket_thuc')->label('Giờ kết thúc')->required(),
                                Forms\Components\TextInput::make('buoi')->label('Buổi')->numeric()->minValue(1)->required(),
                                Forms\Components\TextInput::make('dia_diem')->label('Địa điểm'),
                                Forms\Components\Textarea::make('ghi_chu')->label('Ghi chú'),
                            ])
                            ->createItemButtonLabel('Thêm buổi học')
                            ->collapsed(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('ten_khoa_hoc')->label('Tên khóa học')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('chuongTrinh.ten_chuong_trinh')->label('Chương trình')->sortable(),
                Tables\Columns\BadgeColumn::make('trang_thai')
                    ->label('Trạng thái')
                    ->colors([
                        'primary' => 'ke_hoach',
                        'success' => 'ban_hanh',
                        'warning' => 'chinh_sua',
                        'danger' => 'tam_hoan',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ke_hoach' => 'Kế hoạch',
                        'ban_hanh' => 'Ban hành',
                        'chinh_sua' => 'Chỉnh sửa kế hoạch',
                        'tam_hoan' => 'Tạm hoãn',
                        'dang_dao_tao' => 'Đang đào tạo',
                        'ket_thuc' => 'Kết thúc',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('dangKies_count')
                    ->label('Số HV')
                    ->counts('dangKies')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ngay_bat_dau')->label('Bắt đầu')->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('chuong_trinh_id')
                    ->label('Chương trình')
                    ->options(ChuongTrinh::pluck('ten_chuong_trinh','id')->toArray()),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DangKiesRelationManager::class,
        ];
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
