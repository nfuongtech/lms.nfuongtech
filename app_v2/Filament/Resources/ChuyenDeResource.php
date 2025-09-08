<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChuyenDeResource\Pages;
use App\Models\ChuyenDe;
use Filament\Forms;
use Filament\Forms\Form; // Form đúng
use Filament\Resources\Resource;
use Filament\Tables; // ✅ import Tables namespace
use Filament\Tables\Table; // ✅ Table đúng

class ChuyenDeResource extends Resource
{
    protected static ?string $model = ChuyenDe::class;
    protected static ?string $navigationLabel = 'Chuyên đề';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ten_chuyen_de')
                    ->required()
                    ->label('Tên chuyên đề'),

                Forms\Components\TextInput::make('thoi_luong')
                    ->numeric()
                    ->required()
                    ->label('Thời lượng'),

                Forms\Components\Select::make('dang_ky_id')
                    ->relationship('dangKy', 'ten_hoc_vien')
                    ->label('Học viên')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ten_chuyen_de')->label('Tên chuyên đề'),
                Tables\Columns\TextColumn::make('thoi_luong')->label('Thời lượng'),
                Tables\Columns\TextColumn::make('dangKy.ten_hoc_vien')->label('Học viên'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChuyenDes::route('/'),
            'create' => Pages\CreateChuyenDe::route('/create'),
            'edit' => Pages\EditChuyenDe::route('/{record}/edit'),
        ];
    }
}
