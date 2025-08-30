<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController; // Dòng này rất quan trọng

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route mặc định cho trang chủ
Route::get('/', function () {
    return view('welcome');
});

// Route mới để cung cấp dữ liệu lịch học cho trang chủ
Route::get('/api/schedule-events', [ScheduleController::class, 'getEvents']);
