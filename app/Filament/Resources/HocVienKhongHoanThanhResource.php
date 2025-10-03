<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HocVienKhongHoanThanhResource\Pages;
use App\Models\HocVienKhongHoanThanh;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class HocVienKhongHoanThanhResource extends \Filament\Resources\Resource
{
    protected static ?string $model = HocVienKhongHoanThanh::class;

    protected static ?string $navigationIcon  = 'heroicon-o-x-circle';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationLabel = 'Học viên không hoàn thành';

    public static function getModelLabel(): string
    {
        return 'Học viên không hoàn thành';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Học viên không hoàn thành';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Thông tin')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('hoc_vien_id')
                        ->label('Học viên')
                        ->relationship('hocVien', 'ho_ten') // yêu cầu quan hệ hocVien trong Model
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('khoa_hoc_id')
                        ->label('Khóa học')
                        ->relationship('khoaHoc', 'ma_khoa_hoc') // yêu cầu quan hệ khoaHoc trong Model
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('ket_qua_khoa_hoc_id')
                        ->label('Kết quả KH')
                        ->relationship('ketQuaKhoaHoc', 'id') // yêu cầu quan hệ ketQuaKhoaHoc trong Model
                        ->searchable()
                        ->preload()
                        ->helperText('Liên kết bản ghi tại bảng ket_qua_khoa_hocs')
                        ->nullable(),

                    Forms\Components\Toggle::make('co_the_ghi_danh_lai')
                        ->label('Đề xuất ghi danh lại')
                        ->default(true)
                        ->inline(false),

                    Forms\Components\Textarea::make('ly_do_khong_hoan_thanh')
                        ->label('Lý do không hoàn thành')
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

                Tables\Columns\BadgeColumn::make('ketQuaKhoaHoc.ket_qua')
                    ->label('Kết quả')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'hoan_thanh'       => 'Đạt / Hoàn thành',
                        'khong_hoan_thanh' => 'Không đạt / Không hoàn thành',
                        default            => '—',
                    })
                    ->colors([
                        'success' => 'hoan_thanh',
                        'danger'  => 'khong_hoan_thanh',
                    ])
                    ->toggleable(),

                Tables\Columns\IconColumn::make('co_the_ghi_danh_lai')
                    ->label('Đề xuất học lại')
                    ->boolean()
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ly_do_khong_hoan_thanh')
                    ->label('Lý do')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('khoa_hoc_id')
                    ->label('Khóa học')
                    ->relationship('khoaHoc', 'ma_khoa_hoc'),

                Tables\Filters\TernaryFilter::make('co_the_ghi_danh_lai')
                    ->label('Đề xuất học lại')
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
            'index'  => Pages\ListHocVienKhongHoanThanhs::route('/'),
            'create' => Pages\CreateHocVienKhongHoanThanh::route('/create'),
            'edit'   => Pages\EditHocVienKhongHoanThanh::route('/{record}/edit'),
        ];
    }
}
