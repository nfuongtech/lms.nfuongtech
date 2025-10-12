<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Route;

class HocVienHoanThanhPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'Học viên hoàn thành';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $title = 'Học viên hoàn thành';
    protected static string $view = 'filament.pages.simple-redirect';
    protected static bool $shouldRegisterNavigation = false;

    public static function getSlug(): string
    {
        return 'hoc-vien-hoan-thanhs';
    }

    public function mount(): void
    {
        if (Route::has('filament.admin.resources.hoc-vien-hoan-thanhs.index')) {
            $this->redirectRoute('filament.admin.resources.hoc-vien-hoan-thanhs.index');
        }
    }
}
