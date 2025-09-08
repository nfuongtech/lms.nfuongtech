<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailAccountResource\Pages;
use App\Models\EmailAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailAccountResource extends Resource
{
    protected static ?string $model = EmailAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Tài khoản email';
    protected static ?string $pluralModelLabel = 'Tài khoản email';
    protected static ?string $modelLabel = 'Tài khoản email';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Tên hiển thị')->required(),
            Forms\Components\TextInput::make('email')->email()->required(),
            Forms\Components\TextInput::make('smtp_host')->required(),
            Forms\Components\TextInput::make('smtp_port')->numeric()->default(587),
            Forms\Components\TextInput::make('username')->required(),
            Forms\Components\TextInput::make('password')->password()->required(),
            Forms\Components\Toggle::make('is_active')->label('Kích hoạt'),
            Forms\Components\Toggle::make('is_default')->label('Đặt làm mặc định'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Tên'),
            Tables\Columns\TextColumn::make('email'),
            Tables\Columns\TextColumn::make('smtp_host'),
            Tables\Columns\BooleanColumn::make('is_active')->label('Hoạt động'),
            Tables\Columns\BooleanColumn::make('is_default')->label('Mặc định'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Ngày tạo'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmailAccounts::route('/'),
            'create' => Pages\CreateEmailAccount::route('/create'),
            'edit'   => Pages\EditEmailAccount::route('/{record}/edit'),
        ];
    }
}
