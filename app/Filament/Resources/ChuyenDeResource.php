<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChuyenDeResource\Pages;
use App\Models\ChuyenDe;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ChuyenDeResource extends Resource
{
    protected static ?string $model = ChuyenDe::class;

    protected static ?string $navigationGroup = 'Thiết lập';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $modelLabel = 'Chuyên đề';
    protected static ?string $pluralModelLabel = 'Chuyên đề';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('ma_so')
                    ->label('Mã số')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('ten_chuyen_de')
                    ->label('Tên chuyên đề')
                    ->required(),
                TextInput::make('thoi_luong')
                    ->label('Thời lượng (giờ)')
                    ->numeric()
                    ->required(),
                TextInput::make('doi_tuong_dao_tao')
                    ->label('Đối tượng đào tạo')
                    ->required(),
                Textarea::make('muc_tieu')
                    ->label('Mục tiêu')
                    ->columnSpanFull(),
                Textarea::make('noi_dung')
                    ->label('Nội dung')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ma_so')->label('Mã số')->searchable()->sortable(),
                TextColumn::make('ten_chuyen_de')->label('Tên chuyên đề')->searchable(),
                TextColumn::make('thoi_luong')->label('Thời lượng (giờ)'),
                TextColumn::make('doi_tuong_dao_tao')->label('Đối tượng đào tạo'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
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
