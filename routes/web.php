<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DangKyController;

Route::get('/dang-kies', [DangKyController::class, 'index'])->name('dang-kies.index');
Route::post('/dang-kies/lookup-hocviens', [DangKyController::class, 'lookupHocViens'])->name('dang-kies.lookup');
Route::post('/dang-kies/store', [DangKyController::class, 'store'])->name('dang-kies.store');
