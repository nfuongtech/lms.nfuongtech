<?php

namespace App\Filament\Resources;

use App\Models\QuyTacMaKhoa;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms\Form as FormsForm;
use Filament\Tables\Table as TablesTable;
use App\Filament\Resources\QuyTacMaKhoaResource\Pages;

class QuyTacMaKhoaResource extends Resource
{
    protected static ?string $model = QuyTacMaKhoa::class;

    protected static ?string $navigationIcon = 'heroicon-o-hashtag';
    protected static ?string $navigationGroup = 'Quản lý đào tạo';
    protected static ?string $navigationLabel = 'Quy tắc mã khóa';
    protected static ?string $pluralModelLabel = 'Quy tắc mã khóa';
    protected static ?string $modelLabel = 'Quy tắc mã khóa';

    public static function form(FormsForm $form): FormsForm
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('loai_hinh_dao_tao')
                    ->label('Loại hình đào tạo')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('Ví dụ: Kỹ năng mềm, Đào tạo thường xuyên, Chính quy'),

                Forms\Components\TextInput::make('tien_to')
                    ->label('Tiền tố mã khóa')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('Ví dụ: KNM, ĐTX, CQ'),
            ]);
    }

    public static function table(TablesTable $table): TablesTable
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('loai_hinh_dao_tao')
                    ->label('Loại hình đào tạo')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tien_to')
                    ->label('Tiền tố')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
