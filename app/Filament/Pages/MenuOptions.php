<?php

namespace App\Filament\Pages;

use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MenuOptions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string $view = 'filament.pages.menu-options';

    protected static ?string $navigationLabel = 'Tùy chọn Menu';

    protected static ?string $navigationGroup = 'Thiết lập';

    protected static ?int $navigationSort = 999;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'menus' => MenuItem::toTree(),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        MenuItem::syncFromArray($state['menus'] ?? []);

        Notification::make()
            ->title('Đã lưu tùy chọn menu')
            ->body('Cấu hình menu đã được cập nhật.')
            ->success()
            ->send();

        $this->mount();
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->makeForm()
                ->schema($this->getFormSchema())
                ->statePath('data'),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Cấu hình Menu')
                ->description('Tạo mới, chỉnh sửa và sắp xếp Menu / Sub menu cho thanh điều hướng quản trị.')
                ->schema([
                    Forms\Components\Repeater::make('menus')
                        ->label('Menu chính')
                        ->collapsible()
                        ->reorderable()
                        ->columns(1)
                        ->itemLabel(fn (array $state): string => $state['label'] ?? 'Menu')
                        ->schema($this->menuSchema())
                        ->default([]),
                ]),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected function menuSchema(): array
    {
        return [
            Forms\Components\Hidden::make('id'),
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\TextInput::make('label')
                        ->label('Tên menu')
                        ->required()
                        ->maxLength(150),
                    Forms\Components\TextInput::make('icon')
                        ->label('Icon (tùy chọn)')
                        ->helperText('Nhập tên blade component, ví dụ: heroicon-o-home hoặc ph-icon name.')
                        ->maxLength(150),
                    Forms\Components\TextInput::make('url')
                        ->label('Đường dẫn')
                        ->placeholder('/admin')
                        ->required()
                        ->maxLength(255),
                ])
                ->columns(1)
                ->columnSpanFull(),
            Forms\Components\Repeater::make('children')
                ->label('Sub menu')
                ->collapsed()
                ->default([])
                ->itemLabel(fn (array $state): string => $state['label'] ?? 'Sub menu')
                ->schema($this->submenuSchema())
                ->reorderable(),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected function submenuSchema(): array
    {
        return [
            Forms\Components\Hidden::make('id'),
            Forms\Components\TextInput::make('label')
                ->label('Tên submenu')
                ->required()
                ->maxLength(150),
            Forms\Components\TextInput::make('icon')
                ->label('Icon (tùy chọn)')
                ->helperText('Ví dụ: heroicon-o-list-bullet')
                ->maxLength(150),
            Forms\Components\TextInput::make('url')
                ->label('Đường dẫn')
                ->placeholder('/admin/example')
                ->required()
                ->maxLength(255),
        ];
    }
}
