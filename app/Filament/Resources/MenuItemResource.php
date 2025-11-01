<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use App\Support\MenuIconLibrary;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationGroup = 'Thiết lập';

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationLabel = 'Tùy chỉnh Menu';

    protected static ?string $modelLabel = 'Mục menu';

    protected static ?string $pluralModelLabel = 'Menu';

    protected static ?string $slug = 'menu-items';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thông tin menu')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->label('Tên hiển thị')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\ToggleButtons::make('is_active')
                                    ->label('Trạng thái')
                                    ->boolean()
                                    ->grouped()
                                    ->default(true)
                                    ->colors([
                                        true => 'success',
                                        false => 'danger',
                                    ])
                                    ->icons([
                                        true => 'heroicon-o-check-circle',
                                        false => 'heroicon-o-no-symbol',
                                    ]),
                            ]),
                        Forms\Components\Select::make('parent_id')
                            ->label('Menu cha (nhóm)')
                            ->options(function (?MenuItem $record) {
                                $query = MenuItem::query()
                                    ->where('type', 'group')
                                    ->orderBy('sort');

                                if ($record && $record->exists) {
                                    $query->whereKeyNot($record->getKey());
                                }

                                return $query->pluck('label', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('type')
                            ->label('Loại menu')
                            ->required()
                            ->options([
                                'group' => 'Nhóm chính',
                                'resource' => 'Resource (Filament)',
                                'page' => 'Trang (Filament)',
                                'url' => 'Đường dẫn tùy chỉnh',
                            ])
                            ->default('resource')
                            ->reactive(),
                        Forms\Components\TextInput::make('target')
                            ->label('Resource / Page class')
                            ->datalist(self::targetSuggestions())
                            ->visible(fn (Get $get) => in_array($get('type'), ['resource', 'page']))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('url')
                            ->label('Đường dẫn tùy chỉnh')
                            ->placeholder('https:// hoặc /duong-dan')
                            ->visible(fn (Get $get) => $get('type') === 'url')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sort')
                            ->numeric()
                            ->label('Thứ tự hiển thị')
                            ->helperText('Số nhỏ sẽ xuất hiện trước.')
                            ->default(fn (?MenuItem $record) => $record?->sort ?? (MenuItem::max('sort') + 1)),
                    ]),
                Section::make('Icon hiển thị')
                    ->icon('heroicon-o-sparkles')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('icon_type')
                            ->label('Nguồn icon')
                            ->options([
                                'library' => 'Thư viện Heroicons',
                                'upload' => 'Tải lên icon riêng',
                            ])
                            ->default('library')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'library') {
                                    $set('icon_path', null);
                                }

                                if ($state === 'upload') {
                                    $set('icon', null);
                                }
                            }),
                        Forms\Components\Select::make('icon')
                            ->label('Icon thư viện')
                            ->searchable()
                            ->options(MenuIconLibrary::heroiconOptions())
                            ->visible(fn (Get $get) => $get('icon_type') === 'library')
                            ->helperText('Chọn icon heroicon-* để đồng bộ với Filament.'),
                        Forms\Components\FileUpload::make('icon_path')
                            ->label('Icon tải lên')
                            ->directory('menu-icons')
                            ->image()
                            ->imageEditor()
                            ->visibility('public')
                            ->maxSize(1024)
                            ->hint('Hỗ trợ PNG, JPG, SVG. Kích thước đề xuất 64x64.')
                            ->visible(fn (Get $get) => $get('icon_type') === 'upload'),
                        Forms\Components\ViewField::make('icon_preview')
                            ->label('Xem trước')
                            ->view('filament.components.menu-icon-preview')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('icon')
                    ->label('Icon')
                    ->view('filament.tables.columns.menu-icon-display'),
                TextColumn::make('label')
                    ->label('Tên menu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Loại')
                    ->badge()
                    ->sortable(),
                TextColumn::make('parent.label')
                    ->label('Thuộc nhóm')
                    ->toggleable()
                    ->placeholder('---')
                    ->sortable(),
                TextColumn::make('sort')
                    ->label('Thứ tự')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Hiển thị')
                    ->boolean(),
            ])
            ->defaultSort('parent_id')
            ->defaultSort('sort')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Loại menu')
                    ->options([
                        'group' => 'Nhóm chính',
                        'resource' => 'Resource',
                        'page' => 'Trang',
                        'url' => 'URL',
                    ]),
            ])
            ->actions([
                Action::make('move_up')
                    ->label('Lên')
                    ->icon('heroicon-o-arrow-up')
                    ->action(fn (MenuItem $record) => $record->moveOrder(-1)),
                Action::make('move_down')
                    ->label('Xuống')
                    ->icon('heroicon-o-arrow-down')
                    ->action(fn (MenuItem $record) => $record->moveOrder(1)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMenuItems::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('parent_id')->orderBy('sort');
    }

    /**
     * @return array<string>
     */
    protected static function targetSuggestions(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $suggestions = [];

        $resourcePath = app_path('Filament/Resources');
        if (File::exists($resourcePath)) {
            foreach (File::allFiles($resourcePath) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $class = self::classFromFile($file->getPathname());

                if (! $class) {
                    continue;
                }

                if (is_subclass_of($class, \Filament\Resources\Resource::class)) {
                    $suggestions[$class] = $class;
                }
            }
        }

        $pagePath = app_path('Filament/Pages');
        if (File::exists($pagePath)) {
            foreach (File::allFiles($pagePath) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $class = self::classFromFile($file->getPathname());

                if (! $class) {
                    continue;
                }

                if (is_subclass_of($class, \Filament\Pages\Page::class)) {
                    $suggestions[$class] = $class;
                }
            }
        }

        ksort($suggestions);

        return $cache = array_keys($suggestions);
    }

    protected static function classFromFile(string $path): ?string
    {
        $relative = Str::after($path, app_path() . DIRECTORY_SEPARATOR);
        $class = 'App\\' . str_replace(['/', '.php'], ['\\', ''], $relative);

        return class_exists($class) ? $class : null;
    }
}
