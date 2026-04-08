<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

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

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware(['guest:admin'])
    ->name('admin.login');
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest:admin'])
    ->name('admin.login');

Route::get('/export-csv/{id}', [AdminAttendanceController::class, 'exportCsv'])->name('csv.export');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admins.attendances.index');
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admins.attendances.show');
    Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admins.attendances.update');
    Route::get('/admin/staff/list', [AdminAttendanceController::class, 'usersIndex'])->name('admins.users.index');
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'usersShow'])->name('admins.users.show');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminAttendanceController::class, 'requestsShow'])->name('admins.requests.show');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminAttendanceController::class, 'approval'])->name('admins.requests.approval');
});
// Route::get('/stamp_correction_request/list', [AdminAttendanceController::class, 'requestsIndex'])->middleware(['admin'])->name('admins.requests.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'timeStamp'])->name('attendances.time_stamp');
    Route::post('/attendance', [AttendanceController::class, 'punch'])->name('attendances.punch');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendances.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendances.show');
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'storeRequest'])->name('attendances.request');
});
Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->middleware(['auth', 'verified'])->name('requests.index');



