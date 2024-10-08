<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\RestrictionController;
use App\Http\Controllers\DirectoryController;

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
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{id}', [ReservationController::class, 'show'])->name('reservations.show')->where('id', '[0-9]+');
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

    Route::get('/house', [HouseController::class, 'getHouse'])->name('house.getHouse');

    Route::get('/directory/public', [DirectoryController::class, 'index_public'])->name('directory.index_public');
});

Route::middleware(['auth:sanctum', 'admin:sanctum'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/addresses', [HouseController::class, 'index'])->name('house.index');
    Route::post('/addresses', [HouseController::class, 'store'])->name('house.store');
    Route::delete('/addresses', [HouseController::class, 'destroy'])->name('house.destroy');

    Route::get('/streets', function () {
        return response()->json(\App\Models\Street::all());
    })->name('streets.index');

    Route::get('/configs', [ConfigController::class, 'index'])->name('config.index');
    Route::put('/configs', [ConfigController::class, 'update'])->name('config.update');

    Route::get('/reservations/approval-requests', [ReservationController::class, 'approvalRequestsList'])->name('reservations.approvalRequestsList');
    Route::put('/reservations/approval-requests', [ReservationController::class, 'approvalRequests'])->name('reservations.approvalRequests');
    Route::get('/reservations/approval-requests/count', [ReservationController::class, 'approvalRequestsCount'])->name('reservations.approvalRequestsCount');
    Route::post('/reservations/admin', [ReservationController::class, 'storeAdmin'])->name('reservations.storeAdmin');
    Route::delete('/reservations/admin/{id}', [ReservationController::class, 'remove'])->name('reservations.removeAdmin');


    Route::get('/events', [ReservationController::class, 'events'])->name('reservations.events');
    Route::put('/events/{id}', [ReservationController::class, 'update'])->name('reservations.update');

    Route::post('/restrictions/admin', [RestrictionController::class, 'store'])->name('restrictions.store');
    Route::get('/restrictions/admin', [RestrictionController::class, 'index'])->name('restrictions.index');
    Route::put('/restrictions/admin/{id}', [RestrictionController::class, 'update'])->name('restrictions.update');

    Route::post('/directory', [DirectoryController::class, 'store'])->name('directory.store');
    Route::get('/directory', [DirectoryController::class, 'index'])->name('directory.index');
    Route::put('/directory/{id}', [DirectoryController::class, 'update'])->name('directory.update');
    Route::delete('/directory/{id}', [DirectoryController::class, 'destroy'])->name('directory.destroy');
});
