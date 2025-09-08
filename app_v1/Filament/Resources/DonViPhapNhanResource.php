<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonViPhapNhanResource\Pages;
use App\Models\DonViPhapNhan;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DonViPhapNhanResource extends Resource
{
    protected static ?string $model = DonViPhapNhan::class;

    protected static ?string $navigationGroup = 'Thiết lập';
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $modelLabel = 'Đơn vị pháp nhân';
    protected static ?string $pluralModelLabel = 'Các Đơn vị pháp nhân';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('ma_so_thue')
                    ->label('Mã số thuế')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('ten_don_vi')
                    ->label('Tên đơn vị')
                    ->required(),
                Textarea::make('dia_chi')
                    ->label('Địa chỉ')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('ghi_chu')
                    ->label('Ghi chú')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ma_so_thue')->label('Mã số thuế')->searchable()->sortable(),
                TextColumn::make('ten_don_vi')->label('Tên đơn vị')->searchable(),
                TextColumn::make('dia_chi')->label('Địa chỉ')->searchable()->limit(50),
            ])
            ->filters([
                //
            ])
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
            'index' => Pages\ListDonViPhapNhans::route('/'),
            'create' => Pages\CreateDonViPhapNhan::route('/create'),
            'edit' => Pages\EditDonViPhapNhan::route('/{record}/edit'),
        ];
    }
}
