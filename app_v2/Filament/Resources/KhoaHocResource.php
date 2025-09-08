<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KhoaHocResource\Pages;
use App\Models\KhoaHoc;
use App\Models\ChuyenDe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KhoaHocResource extends Resource
{
    protected static ?string $model = KhoaHoc::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Khóa học';
    protected static ?string $navigationGroup = 'Quản lý đào tạo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('chuyen_de_id')
                    ->label('Chuyên đề')
                    ->options(ChuyenDe::all()->pluck('ten_chuyen_de', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('ma_khoa')
                    ->label('Mã khóa học')
                    ->disabled()
                    ->dehydrated(true), // lưu vào DB
                 
                Forms\Components\TextInput::make('ten_khoa_hoc')
                    ->label('Tên khóa học')
                    ->required(),

                Forms\Components\TextInput::make('nam')
                    ->numeric()
                    ->label('Năm')
                    ->required(),

                Forms\Components\Select::make('trang_thai')
                    ->label('Trạng thái')
                    ->options([
                        'Soạn thảo' => 'Soạn thảo',
                        'Đang diễn ra' => 'Đang diễn ra',
                        'Hoàn thành' => 'Hoàn thành',
                    ])
                    ->default('Soạn thảo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ma_khoa')->label('Mã khóa'),
                Tables\Columns\TextColumn::make('ten_khoa_hoc')->label('Tên khóa'),
                Tables\Columns\TextColumn::make('nam')->label('Năm'),
                Tables\Columns\TextColumn::make('trang_thai')->label('Trạng thái'),
                Tables\Columns\TextColumn::make('chuyenDe.ten_chuyen_de')->label('Chuyên đề'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
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
