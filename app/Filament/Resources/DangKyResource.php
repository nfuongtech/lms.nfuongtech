<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DangKyResource\Pages;
use App\Models\DangKy;
use App\Models\HocVien; // Thêm dòng này
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DangKyResource extends Resource
{
    protected static ?string $model = DangKy::class;

    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $modelLabel = 'Ghi danh';
    protected static ?string $pluralModelLabel = 'Ghi danh';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('khoa_hoc_id')
                    ->relationship('khoaHoc', 'ten_khoa_hoc')
                    ->label('Chọn Khóa học')
                    ->required(),
                Select::make('hoc_vien_id')
                    // ->relationship('hocVien', 'ho_ten') // Xóa dòng này
                    ->options(HocVien::pluck('ho_ten', 'id')) // Thay thế bằng dòng này
                    ->label('Chọn Học viên')
                    ->multiple()
                    ->required()
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('msnv')->label('MSNV')->required()->unique(),
                        TextInput::make('ho_ten')->label('Họ và tên')->required(),
                        TextInput::make('email')->label('Email')->email()->unique(),
                        TextInput::make('chuc_vu')->label('Chức vụ'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('hocVien.ho_ten')->label('Họ tên Học viên')->searchable(),
                TextColumn::make('khoaHoc.ten_khoa_hoc')->label('Tên Khóa học')->searchable(),
                TextColumn::make('created_at')->label('Ngày ghi danh')->dateTime('d/m/Y'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListDangKies::route('/'),
            'create' => Pages\CreateDangKy::route('/create'),
            'edit' => Pages\EditDangKy::route('/{record}/edit'),
        ];
    }
}
