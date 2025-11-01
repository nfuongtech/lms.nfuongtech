<?php

namespace App\Filament\Resources\AdminNavigationItemResource\RelationManagers;

use App\Models\AdminNavigationItem;
use App\Support\FilamentMenuOptions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $title = 'Sub menu';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Sub menu')->schema([
                Grid::make(3)->schema([
                    TextInput::make('title')
                        ->label('Tên menu')
                        ->required()
                        ->maxLength(120),

                    TextInput::make('handle')
                        ->label('Định danh')
                        ->maxLength(120)
                        ->unique(ignoreRecord: true)
                        ->disabled(fn (?AdminNavigationItem $record) => $record !== null),

                    TextInput::make('sort')
                        ->numeric()
                        ->label('Thứ tự')
                        ->default(0),
                ]),

                Grid::make(2)->schema([
                    Select::make('type')
                        ->label('Loại menu')
                        ->options([
                            'resource' => 'Resource',
                            'page'     => 'Page',
                            'link'     => 'Liên kết ngoài',
                        ])
                        ->required()
                        ->live(),

                    Toggle::make('open_in_new_tab')
                        ->label('Mở tab mới')
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

                Toggle::make('is_active')->label('Kích hoạt'),

                Select::make('icon_source')
                    ->label('Nguồn icon')
                    ->options([
                        'heroicon' => 'Heroicons',
                        'upload'   => 'Tải lên',
                        'custom'   => 'Tùy chỉnh',
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
                    ->afterStateUpdated(fn (Set $set, $state) => $set('icon_name', $state)),

                TextInput::make('icon_name')
                    ->label('Alias icon tùy chỉnh')
                    ->visible(fn (Get $get) => $get('icon_source') === 'custom')
                    ->maxLength(120)
                    ->default(fn (?AdminNavigationItem $record) => $record?->icon_source === 'custom' ? $record->icon_name : null)
                    ->required(fn (Get $get) => $get('icon_source') === 'custom'),

                FileUpload::make('icon_path')
                    ->label('Tải icon lên')
                    ->visible(fn (Get $get) => $get('icon_source') === 'upload')
                    ->disk(config('admin-navigation.upload_disk'))
                    ->directory(config('admin-navigation.upload_directory'))
                    ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/jpeg', 'image/webp'])
                    ->preserveFilenames()
                    ->imageEditor(false),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Sub menu')->searchable()->sortable(),
                TextColumn::make('type')->label('Loại')->badge(),
                IconColumn::make('is_active')->label('Hiển thị')->boolean(),
            ])
            ->defaultSort('sort')
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
                ]),
            ]);
    }
}
