<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Models
use App\Models\DangKy;
use App\Models\DiemDanh;
use App\Models\KetQuaKhoaHoc;

// Observers
use App\Observers\DangKyObserver;
use App\Observers\DiemDanhBuoiHocObserver;
use App\Observers\KetQuaKhoaHocObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DangKy::observe(DangKyObserver::class);
        DiemDanh::observe(DiemDanhBuoiHocObserver::class);
        KetQuaKhoaHoc::observe(KetQuaKhoaHocObserver::class);
    }
}
