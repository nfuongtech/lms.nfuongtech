<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TuyChonKetQuaResource\Pages;
use App\Models\TuyChonKetQua;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TuyChonKetQuaResource extends Resource
{
    protected static ?string $model = TuyChonKetQua::class;

    protected static ?string $navigationGroup = 'Thiết lập';
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $modelLabel = 'Tùy chọn Kết quả';
    protected static ?string $pluralModelLabel = 'Tùy chọn Kết quả';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('loai')
                    ->label('Loại tùy chọn')
                    ->options([
                        'chuyen_can' => 'Chuyên cần',
                        'ket_qua' => 'Kết quả',
                    ])
                    ->required(),
                TextInput::make('gia_tri')
                    ->label('Giá trị tùy chọn')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('loai')->label('Loại tùy chọn'),
                TextColumn::make('gia_tri')->label('Giá trị'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListTuyChonKetQuas::route('/'),
        ];
    }
}
