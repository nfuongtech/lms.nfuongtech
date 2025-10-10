<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KhoaHocResource;
use Filament\Resources\Pages\Page;

class CalendarKhoaHoc extends Page
{
    protected static string $resource = KhoaHocResource::class;

    protected static string $view = 'filament.resources.khoa-hoc-resource.pages.calendar-khoa-hoc';
}
