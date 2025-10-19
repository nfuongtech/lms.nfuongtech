<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiaDiemDaoTaoResource\Pages;
use App\Models\DiaDiemDaoTao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DiaDiemDaoTaoResource extends Resource
{
    protected static ?string $model = DiaDiemDaoTao::class;
    protected static ?string $navigationGroup = 'Thiết lập';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Địa điểm đào tạo';
    protected static ?string $modelLabel = 'địa điểm đào tạo';
    protected static ?string $pluralModelLabel = 'Địa điểm đào tạo';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('ma_phong')->label('Mã phòng')->required()->maxLength(50),
            Forms\Components\TextInput::make('ten_phong')->label('Tên phòng học')->required(),
            Forms\Components\TextInput::make('hv_toi_da')->label('HV tối đa')->numeric()->required(),
            Forms\Components\Textarea::make('co_so_vat_chat')->label('Cơ sở vật chất')->rows(4),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('rowIndex')->label('TT')->rowIndex(),
            Tables\Columns\TextColumn::make('ma_phong')->label('Mã phòng')->searchable(),
            Tables\Columns\TextColumn::make('ten_phong')->label('Tên phòng'),
            Tables\Columns\TextColumn::make('hv_toi_da')->label('HV tối đa')->alignRight(),
            Tables\Columns\TextColumn::make('co_so_vat_chat')->label('Cơ sở vật chất')->wrap(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDiaDiemDaoTaos::route('/'),
            'create' => Pages\CreateDiaDiemDaoTao::route('/create'),
            'edit'   => Pages\EditDiaDiemDaoTao::route('/{record}/edit'),
        ];
    }
}
