<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminNavigationItemResource\Pages;
use App\Filament\Resources\AdminNavigationItemResource\RelationManagers\ChildrenRelationManager;
use App\Models\AdminNavigationItem;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminNavigationItemResource extends Resource
{
    protected static ?string $model = AdminNavigationItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Thiết lập';

    protected static ?int $navigationSort = 96;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thông tin menu')
                    ->columns(2)
                    ->schema([
                        TextInput::make('label')
                            ->label('Tên hiển thị')
                            ->required()
                            ->maxLength(255),
                        Select::make('parent_id')
                            ->label('Menu cha')
                            ->searchable()
                            ->options(fn (Get $get, ?AdminNavigationItem $record) => AdminNavigationItem::query()
                                ->root()
                                ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                ->orderBy('label')
                                ->pluck('label', 'id'))
                            ->placeholder('Không (Menu cấp 1)'),
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
                            ->helperText('Nhập tên route, class resource hoặc URL tùy theo loại liên kết')
                            ->required(),
                        TextInput::make('icon')
                            ->label('Heroicon')
                            ->placeholder('heroicon-o-home')
                            ->helperText('Sử dụng tên icon Heroicons hoặc icon hỗ trợ bởi Filament'),
                        TextInput::make('badge')
                            ->label('Badge')
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->label('Thứ tự sắp xếp')
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
                TextColumn::make('label')
                    ->label('Tên hiển thị')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.label')
                    ->label('Menu cha')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Loại')
                    ->badge(),
                TextColumn::make('target')
                    ->label('Mục tiêu')
                    ->limit(40)
                    ->tooltip(fn (AdminNavigationItem $record) => $record->target),
                TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean(),
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
            ChildrenRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminNavigationItems::route('/'),
            'create' => Pages\CreateAdminNavigationItem::route('/create'),
            'edit' => Pages\EditAdminNavigationItem::route('/{record}/edit'),
        ];
    }
}
