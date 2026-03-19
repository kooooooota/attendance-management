<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'timeStamp'])->name('attendances.time_stamp');
    Route::post('/attendance', [AttendanceController::class, 'punch'])->name('attendances.punch');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendances.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendances.show');
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'storeRequest'])->name('attendances.attendance_request');

});
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');
Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->middleware(['auth', 'verified'])->name('attendances.request_list');
