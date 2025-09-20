<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DangKyController;
use App\Http\Controllers\ChuyenDeExportController;

// Các route cho Đăng ký
Route::get('/dang-kies', [DangKyController::class, 'index'])->name('dang-kies.index');
Route::post('/dang-kies/lookup-hocviens', [DangKyController::class, 'lookupHocViens'])->name('dang-kies.lookup');
Route::post('/dang-kies/store', [DangKyController::class, 'store'])->name('dang-kies.store');

// Route Xuất Excel cho Chuyên đề/Học phần
Route::get('/export/chuyende', [ChuyenDeExportController::class, 'export'])
    ->middleware(['web', 'auth']) // đảm bảo chỉ người đăng nhập mới xuất được
    ->name('export.chuyende');
