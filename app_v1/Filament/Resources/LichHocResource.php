<?php
namespace App\Filament\Resources;

use App\Models\LichHoc;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use App\Filament\Resources\LichHocResource\Pages;

class LichHocResource extends Resource
{
    protected static ?string $model = LichHoc::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Lịch học';

    // Form dùng đúng namespace
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('khoa_hoc_id')
                    ->relationship('khoaHoc', 'ma_khoa_hoc')
                    ->label('Khóa học')
                    ->required(),
                Forms\Components\DatePicker::make('ngay_hoc')->label('Ngày học')->required(),
                Forms\Components\TimePicker::make('gio_bat_dau')->label('Giờ bắt đầu')->required(),
                Forms\Components\TimePicker::make('gio_ket_thuc')->label('Giờ kết thúc')->required(),
            ]);
    }

    // Table dùng đúng namespace
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('khoaHoc.ma_khoa_hoc')->label('Khóa học'),
                Tables\Columns\TextColumn::make('ngay_hoc')->label('Ngày học')->date(),
                Tables\Columns\TextColumn::make('gio_bat_dau')->label('Bắt đầu'),
                Tables\Columns\TextColumn::make('gio_ket_thuc')->label('Kết thúc'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->filters([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLichHocs::route('/'),
            'create' => Pages\CreateLichHoc::route('/create'),
            'edit' => Pages\EditLichHoc::route('/{record}/edit'),
        ];
    }
}
