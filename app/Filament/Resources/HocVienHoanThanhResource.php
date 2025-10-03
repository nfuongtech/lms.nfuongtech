<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HocVienHoanThanhResource\Pages;
use App\Models\HocVienHoanThanh;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class HocVienHoanThanhResource extends \Filament\Resources\Resource
{
    protected static ?string $model = HocVienHoanThanh::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationLabel = 'Học viên hoàn thành';

    public static function getModelLabel(): string
    {
        return 'Học viên hoàn thành';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Học viên hoàn thành';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Thông tin')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('hoc_vien_id')
                        ->label('Học viên')
                        ->relationship('hocVien', 'ho_ten')   // CẦN quan hệ hocVien trong Model
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('khoa_hoc_id')
                        ->label('Khóa học')
                        ->relationship('khoaHoc', 'ma_khoa_hoc') // CẦN quan hệ khoaHoc trong Model
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('ket_qua_khoa_hoc_id')
                        ->label('Kết quả KH')
                        ->relationship('ketQuaKhoaHoc', 'id') // CẦN quan hệ ketQuaKhoaHoc trong Model
                        ->searchable()
                        ->preload()
                        ->helperText('Liên kết bản ghi tại bảng ket_qua_khoa_hocs')
                        ->nullable(),

                    Forms\Components\DatePicker::make('ngay_hoan_thanh')
                        ->label('Ngày hoàn thành')
                        ->native(false)
                        ->closeOnDateSelection()
                        ->displayFormat('d/m/Y')
                        ->required(),

                    Forms\Components\Toggle::make('chung_chi_da_cap')
                        ->label('Đã cấp chứng chỉ')
                        ->inline(false)
                        ->default(false),

                    Forms\Components\Textarea::make('ghi_chu')
                        ->label('Ghi chú')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hocVien.msnv')
                    ->label('MSNV')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('hocVien.ho_ten')
                    ->label('Họ tên')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('khoaHoc.ma_khoa_hoc')
                    ->label('Mã khóa')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ketQuaKhoaHoc.ket_qua')
                    ->label('Kết quả')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'hoan_thanh' => 'Đạt / Hoàn thành',
                        'khong_hoan_thanh' => 'Không đạt / Không hoàn thành',
                        default => '—',
                    })
                    ->badge()
                    ->colors([
                        'success' => 'hoan_thanh',
                        'danger' => 'khong_hoan_thanh',
                    ])
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ngay_hoan_thanh')
                    ->label('Ngày hoàn thành')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('chung_chi_da_cap')
                    ->label('Cấp C.C.')
                    ->boolean()
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ghi_chu')
                    ->label('Ghi chú')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('khoa_hoc_id')
                    ->label('Khóa học')
                    ->relationship('khoaHoc', 'ma_khoa_hoc'),

                Tables\Filters\TernaryFilter::make('chung_chi_da_cap')
                    ->label('Đã cấp chứng chỉ')
                    ->nullable(),
            ])

            ->actions([
                Tables\Actions\EditAction::make()->label('Sửa'),
                Tables\Actions\DeleteAction::make()->label('Xóa'),
            ])

            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('Xóa đã chọn'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHocVienHoanThanhs::route('/'),
            'create' => Pages\CreateHocVienHoanThanh::route('/create'),
            'edit' => Pages\EditHocVienHoanThanh::route('/{record}/edit'),
        ];
    }
}
