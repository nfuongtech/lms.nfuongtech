<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\KetQuaKhoaHoc;
use App\Observers\KetQuaKhoaHocObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        KetQuaKhoaHoc::observe(KetQuaKhoaHocObserver::class);
    }
}

