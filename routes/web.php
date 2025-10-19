<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DangKyController;
use App\Http\Controllers\ChuyenDeExportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\SiteLoginController;

// Các route cho Đăng ký
Route::get('/dang-kies', [DangKyController::class, 'index'])->name('dang-kies.index');
Route::post('/dang-kies/lookup-hocviens', [DangKyController::class, 'lookupHocViens'])->name('dang-kies.lookup');
Route::post('/dang-kies/store', [DangKyController::class, 'store'])->name('dang-kies.store');

// Route Xuất Excel cho Chuyên đề/Học phần
Route::get('/export/chuyende', [ChuyenDeExportController::class, 'export'])
    ->middleware(['web', 'auth']) // đảm bảo chỉ người đăng nhập mới xuất được
    ->name('export.chuyende');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/khoa-hoc/{khoaHoc}/hoc-vien', [HomeController::class, 'registeredStudents'])->name('home.registrations');
Route::get('/tra-cuu-ket-qua', [HomeController::class, 'lookupResults'])->name('home.lookup');

// Đăng nhập / Đăng xuất trên trang chủ
Route::post('/login',  [SiteLoginController::class, 'login'])->name('site.login');
Route::post('/logout', [SiteLoginController::class, 'logout'])->name('site.logout');
