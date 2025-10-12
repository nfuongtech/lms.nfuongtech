<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Route;

class HocVienKhongHoanThanhPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationLabel = 'Học viên không hoàn thành';
    protected static ?string $navigationGroup = 'Đào tạo';
    protected static ?string $title = 'Học viên không hoàn thành';
    protected static string $view = 'filament.pages.simple-redirect';
    protected static bool $shouldRegisterNavigation = false;

    public static function getSlug(): string
    {
        return 'hoc-vien-khong-hoan-thanhs';
    }

    public function mount(): void
    {
        if (Route::has('filament.admin.resources.hoc-vien-khong-hoan-thanhs.index')) {
            $this->redirectRoute('filament.admin.resources.hoc-vien-khong-hoan-thanhs.index');
        }
    }
}
