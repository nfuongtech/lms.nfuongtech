<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuyTacMaKhoaResource\Pages;
use App\Models\QuyTacMaKhoa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuyTacMaKhoaResource extends Resource
{
    protected static ?string $model = QuyTacMaKhoa::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'Quy tắc mã khóa';
    protected static ?string $modelLabel = 'quy tắc mã khóa';
    protected static ?string $pluralModelLabel = 'Quy tắc mã khóa';
    protected static ?string $navigationGroup = 'Thiết lập';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('loai_hinh_dao_tao')
                    ->label('Loại hình đào tạo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('tien_to')
                    ->label('Tiền tố mã')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(10)
                    ->helperText('Ví dụ: KNM, ĐTX, KNS,...'),
                
                Forms\Components\TextInput::make('dinh_dang')
                    ->label('Định dạng')
                    ->default('YYMMSSS')
                    ->disabled()
                    ->helperText('Định dạng: Năm(2 số) + Tháng(2 số) + Số thứ tự(3 số)'),
                
                Forms\Components\TextInput::make('mau_so')
                    ->label('Mẫu số hiện tại')
                    ->numeric()
                    ->default(0)
                    ->helperText('Số thứ tự cuối cùng được sử dụng'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('loai_hinh_dao_tao')
                    ->label('Loại hình đào tạo')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('tien_to')
                    ->label('Tiền tố')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('dinh_dang')
                    ->label('Định dạng'),
                
                Tables\Columns\TextColumn::make('mau_so')
                    ->label('Mẫu số')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuyTacMaKhoas::route('/'),
            'create' => Pages\CreateQuyTacMaKhoa::route('/create'),
            'edit' => Pages\EditQuyTacMaKhoa::route('/{record}/edit'),
        ];
    }
}
