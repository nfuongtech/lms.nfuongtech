<?php

namespace App\Filament\Resources\AdminNavigationItemResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Sub menu')
                    ->columns(2)
                    ->schema([
                        TextInput::make('label')
                            ->label('Tên hiển thị')
                            ->required(),
                        Select::make('type')
                            ->label('Loại liên kết')
                            ->options([
                                'route' => 'Route trong hệ thống',
                                'resource' => 'Filament Resource',
                                'url' => 'Đường dẫn bên ngoài',
                            ])
                            ->default('route')
                            ->required(),
                        TextInput::make('target')
                            ->label('Giá trị mục tiêu')
                            ->required(),
                        TextInput::make('icon')
                            ->label('Heroicon')
                            ->placeholder('heroicon-o-document-text'),
                        TextInput::make('badge')
                            ->label('Badge'),
                        TextInput::make('sort_order')
                            ->label('Thứ tự')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Kích hoạt')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Tên hiển thị')->sortable(),
                TextColumn::make('type')->label('Loại')->badge(),
                TextColumn::make('target')->label('Mục tiêu')->limit(40),
                TextColumn::make('sort_order')->label('Thứ tự')->sortable(),
                IconColumn::make('is_active')->label('Trạng thái')->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
