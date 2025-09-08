<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LichHocResource\Pages;
use App\Models\LichHoc;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LichHocResource extends Resource
{
    protected static ?string $model = LichHoc::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Lịch học';
    protected static ?string $pluralLabel = 'Danh sách lịch học';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('khoa_hoc_id')
                ->label('Khóa học')
                ->relationship('khoaHoc', 'ten_khoa_hoc')
                ->required(),

            Forms\Components\DatePicker::make('ngay_hoc')
                ->label('Ngày học')
                ->required(),

            Forms\Components\TextInput::make('phong_hoc')
                ->label('Phòng học')
                ->required(),

            Forms\Components\Textarea::make('ghi_chu')
                ->label('Ghi chú'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('khoaHoc.ten_khoa_hoc')->label('Khóa học'),
            Tables\Columns\TextColumn::make('ngay_hoc')->label('Ngày học')->date(),
            Tables\Columns\TextColumn::make('phong_hoc')->label('Phòng học'),
            Tables\Columns\TextColumn::make('ghi_chu')->label('Ghi chú')->limit(30),
        ]);
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
