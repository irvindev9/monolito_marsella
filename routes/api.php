<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HouseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
});

Route::middleware(['auth:sanctum', 'admin:sanctum'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');

    Route::get('/addresses', [HouseController::class, 'index'])->name('house.index');
});
