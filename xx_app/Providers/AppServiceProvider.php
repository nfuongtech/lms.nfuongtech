<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Đăng ký Observers ở đây
use App\Models\KetQuaKhoaHoc;
use App\Models\DiemDanh;
use App\Observers\KetQuaKhoaHocObserver;
use App\Observers\DiemDanhObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Đăng ký service/container binding nếu cần
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bọc class_exists để tránh lỗi khi chạy migrate ban đầu hoặc khi class chưa sẵn sàng
        if (class_exists(KetQuaKhoaHoc::class) && class_exists(KetQuaKhoaHocObserver::class)) {
            KetQuaKhoaHoc::observe(KetQuaKhoaHocObserver::class);
        }

        if (class_exists(DiemDanh::class) && class_exists(DiemDanhObserver::class)) {
            DiemDanh::observe(DiemDanhObserver::class);
        }
    }
}
