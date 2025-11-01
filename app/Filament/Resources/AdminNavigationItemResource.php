<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminNavigationItemResource\Pages;
use App\Filament\Resources\AdminNavigationItemResource\RelationManagers\ChildrenRelationManager;
use App\Models\AdminNavigationItem;
use App\Support\FilamentMenuOptions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminNavigationItemResource extends Resource
{
    protected static ?string $model = AdminNavigationItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3-center-left';

    protected static ?string $navigationLabel = 'Tùy chỉnh Menu';

    protected static ?string $navigationGroup = 'Thiết lập';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::baseFormSchema());
    }

    public static function baseFormSchema(): array
    {
        return [
            Section::make('Thông tin menu')->schema([
                Grid::make(2)->schema([
                    TextInput::make('title')
                        ->label('Tên menu')
                        ->required()
                        ->maxLength(120)
                        ->helperText('Tên hiển thị trong sidebar.'),

                    TextInput::make('handle')
                        ->label('Định danh')
                        ->maxLength(120)
                        ->unique(ignoreRecord: true)
                        ->helperText('Sử dụng để đồng bộ hoặc nhập/xuất cấu hình.')
                        ->disabled(fn (?AdminNavigationItem $record) => $record !== null),
                ]),

                Grid::make(3)->schema([
                    Select::make('navigation_group')
                        ->label('Nhóm menu')
                        ->options(fn () => AdminNavigationItem::query()
                            ->whereNotNull('navigation_group')
                            ->distinct()
                            ->orderBy('navigation_group')
                            ->pluck('navigation_group', 'navigation_group')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->helperText('Nhóm hiển thị ở sidebar. Bỏ trống để dùng mặc định.'),

                    Select::make('parent_id')
                        ->label('Menu cha')
                        ->options(function (?AdminNavigationItem $record) {
                            return AdminNavigationItem::query()
                                ->root()
                                ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->helperText('Bỏ trống để là menu cấp 1.'),

                    TextInput::make('sort')
                        ->numeric()
                        ->label('Thứ tự hiển thị')
                        ->default(0)
                        ->helperText('Giá trị nhỏ sẽ nằm trên cùng.'),
                ]),

                Toggle::make('is_active')
                    ->label('Kích hoạt')
                    ->helperText('Tắt để ẩn khỏi sidebar.'),
            ])->columns(1),

            Section::make('Liên kết & hành vi')->schema([
                Grid::make(2)->schema([
                    Select::make('type')
                        ->label('Loại menu')
                        ->options([
                            'resource' => 'Resource (Filament Resource)',
                            'page'     => 'Trang tùy chỉnh',
                            'link'     => 'Liên kết ngoài',
                        ])
                        ->required()
                        ->live(),

                    Toggle::make('open_in_new_tab')
                        ->label('Mở trong tab mới')
                        ->visible(fn (Get $get) => $get('type') === 'link'),
                ]),

                Select::make('target')
                    ->label('Chọn Resource / Page')
                    ->visible(fn (Get $get) => in_array($get('type'), ['resource', 'page']))
                    ->options(function (Get $get) {
                        return $get('type') === 'page'
                            ? FilamentMenuOptions::pageOptions()
                            : FilamentMenuOptions::resourceOptions();
                    })
                    ->searchable()
                    ->preload(),

                TextInput::make('external_url')
                    ->label('Đường dẫn ngoài')
                    ->placeholder('https://example.com')
                    ->visible(fn (Get $get) => $get('type') === 'link')
                    ->url(),
            ])->columns(1),

            Section::make('Icon')->schema([
                Select::make('icon_source')
                    ->label('Nguồn icon')
                    ->options([
                        'heroicon' => 'Heroicons (gợi ý sẵn)',
                        'upload'   => 'Tải lên icon',
                        'custom'   => 'Tùy chỉnh Blade icon',
                    ])
                    ->required()
                    ->default('heroicon')
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state !== 'upload') {
                            $set('icon_path', null);
                        }

                        if ($state !== 'custom') {
                            $set('icon_name', null);
                        }
                    }),

                Select::make('heroicon_choice')
                    ->label('Chọn icon Heroicons')
                    ->options(fn () => FilamentMenuOptions::heroiconOptions())
                    ->visible(fn (Get $get) => $get('icon_source') === 'heroicon')
                    ->searchable()
                    ->preload()
                    ->dehydrated(false)
                    ->default(fn (?AdminNavigationItem $record) => $record?->icon_source === 'heroicon' ? $record->icon_name : null)
                    ->afterStateUpdated(fn (Set $set, $state) => $set('icon_name', $state))
                    ->helperText('Lọc theo tên để xem gợi ý icon.'),

                TextInput::make('icon_name')
                    ->label('Alias icon tùy chỉnh')
                    ->visible(fn (Get $get) => $get('icon_source') === 'custom')
                    ->maxLength(120)
                    ->default(fn (?AdminNavigationItem $record) => $record?->icon_source === 'custom' ? $record->icon_name : null)
                    ->helperText('Nhập tên icon dạng blade component, ví dụ: `heroicon-s-home`.')
                    ->required(fn (Get $get) => $get('icon_source') === 'custom'),

                FileUpload::make('icon_path')
                    ->label('Tải lên icon')
                    ->visible(fn (Get $get) => $get('icon_source') === 'upload')
                    ->disk(config('admin-navigation.upload_disk'))
                    ->directory(config('admin-navigation.upload_directory'))
                    ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/jpeg', 'image/webp'])
                    ->helperText('Khuyến nghị SVG nền trong suốt, kích thước 24x24.')
                    ->preserveFilenames()
                    ->imageEditor(false),
            ])->columns(1),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Menu')->searchable()->sortable(),
                TextColumn::make('navigation_group')->label('Nhóm')->sortable()->toggleable(),
                TextColumn::make('type')->label('Loại')->sortable()->badge(),
                TextColumn::make('sort')->label('Thứ tự')->sortable(),
                IconColumn::make('is_active')->label('Hiển thị')->boolean(),
            ])
            ->defaultSort('sort')
            ->filters([
                Tables\Filters\SelectFilter::make('navigation_group')
                    ->label('Nhóm')
                    ->options(fn () => AdminNavigationItem::query()
                        ->whereNotNull('navigation_group')
                        ->distinct()
                        ->orderBy('navigation_group')
                        ->pluck('navigation_group', 'navigation_group')
                        ->all()),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('move_up')
                        ->label('Đưa lên')
                        ->icon('heroicon-o-arrow-up')
                        ->action(fn (AdminNavigationItem $record) => $record->moveUp()),
                    Action::make('move_down')
                        ->label('Đưa xuống')
                        ->icon('heroicon-o-arrow-down')
                        ->action(fn (AdminNavigationItem $record) => $record->moveDown()),
                    EditAction::make(),
                    DeleteAction::make(),
                ])->tooltip('Tùy chọn'),
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
