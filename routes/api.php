<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\HealthcareController;
use App\Http\Controllers\Api\UserController;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('healthcares', [HealthcareController::class, 'index'])->name('healthcares.index');
    Route::post('book-appointment', [BookingController::class, 'book'])->name('appointment.book');
    Route::get('user-appointments', [UserController::class, 'appointments'])->name('user.appointment');
    Route::post('cancel-appointment', [BookingController::class, 'cancelAppointment'])->name('cancel.appointment');
    Route::post('complete-appointment', [BookingController::class, 'completeAppointment'])->name('complete.appointment');
});
