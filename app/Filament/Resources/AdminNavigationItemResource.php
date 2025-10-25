<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminNavigationItemResource\Pages;
use App\Models\AdminNavigationItem;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Filament\Pages\Page;
use Filament\Resources\Resource as FilamentResource;

class AdminNavigationItemResource extends Resource
{
    protected static ?string $model = AdminNavigationItem::class;

    protected static ?string $navigationGroup = 'Thiết lập';

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationLabel = 'Tùy chọn Menu';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(1)->schema([
                TextInput::make('title')
                    ->label('Tên hiển thị')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Loại mục')
                    ->options([
                        'group' => 'Nhóm menu',
                        'resource' => 'Resource',
                        'page' => 'Trang Filament',
                        'url' => 'Liên kết tuỳ chỉnh',
                    ])
                    ->required()
                    ->reactive(),
                Select::make('parent_id')
                    ->label('Thuộc nhóm')
                    ->options(fn () => AdminNavigationItem::query()
                        ->where('type', 'group')
                        ->orderBy('sort')
                        ->pluck('title', 'id'))
                    ->searchable()
                    ->visible(fn (Forms\Get $get) => $get('type') !== 'group')
                    ->required(fn (Forms\Get $get) => $get('type') !== 'group'),
                Select::make('target')
                    ->label('Đích điều hướng')
                    ->options(fn (Forms\Get $get) => match ($get('type')) {
                        'resource' => static::getResourceOptions(),
                        'page' => static::getPageOptions(),
                        default => [],
                    })
                    ->searchable()
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['resource', 'page'], true))
                    ->required(fn (Forms\Get $get) => in_array($get('type'), ['resource', 'page'], true)),
                TextInput::make('url')
                    ->label('Đường dẫn')
                    ->visible(fn (Forms\Get $get) => $get('type') === 'url')
                    ->required(fn (Forms\Get $get) => $get('type') === 'url')
                    ->maxLength(255),
                TextInput::make('icon')
                    ->label('Biểu tượng (Heroicon hoặc Blade icon)')
                    ->maxLength(255),
                TextInput::make('sort')
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
                TextColumn::make('title')->label('Tên')->searchable()->sortable(),
                TextColumn::make('type')->label('Loại')->badge(),
                TextColumn::make('parent.title')->label('Nhóm')->toggleable(),
                TextColumn::make('icon')->label('Icon')->toggleable(),
                TextColumn::make('sort')->label('Thứ tự')->sortable(),
                IconColumn::make('is_active')->label('Hiển thị')->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'group' => 'Nhóm menu',
                        'resource' => 'Resource',
                        'page' => 'Trang Filament',
                        'url' => 'Liên kết',
                    ]),
            ])
            ->reorderable('sort')
            ->defaultSort('sort');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminNavigationItems::route('/'),
            'create' => Pages\CreateAdminNavigationItem::route('/create'),
            'edit' => Pages\EditAdminNavigationItem::route('/{record}/edit'),
        ];
    }

    protected static function getResourceOptions(): array
    {
        $resourcePath = app_path('Filament/Resources');

        if (! File::isDirectory($resourcePath)) {
            return [];
        }

        return collect(File::allFiles($resourcePath))
            ->map(function ($file) {
                $relativePath = Str::of($file->getRelativePathname())
                    ->replaceLast('.php', '')
                    ->replace('/', '\\');

                return 'App\\Filament\\Resources\\' . $relativePath;
            })
            ->filter(fn ($class) => class_exists($class) && is_subclass_of($class, FilamentResource::class))
            ->mapWithKeys(function ($class) {
                $label = method_exists($class, 'getNavigationLabel')
                    ? $class::getNavigationLabel()
                    : class_basename($class);

                return [$class => $label];
            })
            ->sort()
            ->toArray();
    }

    protected static function getPageOptions(): array
    {
        $pagePath = app_path('Filament/Pages');

        if (! File::isDirectory($pagePath)) {
            return [];
        }

        return collect(File::allFiles($pagePath))
            ->map(function ($file) {
                $relativePath = Str::of($file->getRelativePathname())
                    ->replaceLast('.php', '')
                    ->replace('/', '\\');

                return 'App\\Filament\\Pages\\' . $relativePath;
            })
            ->filter(fn ($class) => class_exists($class) && is_subclass_of($class, Page::class))
            ->mapWithKeys(function ($class) {
                $label = method_exists($class, 'getNavigationLabel')
                    ? $class::getNavigationLabel()
                    : class_basename($class);

                return [$class => $label];
            })
            ->sort()
            ->toArray();
    }
}
