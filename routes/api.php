<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['jwt.auth'])->group(function () {
    Route::prefix('participant')->group(function () {
        Route::post('/create', [ParticipantController::class, 'createParticipant']);
        Route::get('/read-all', [ParticipantController::class, 'getAllParticipants']);
    });

    Route::prefix('room')->group(function () {
        Route::post('/create', [RoomController::class, 'createRoom']);
        Route::get('/read-all', [RoomController::class, 'getAllRooms']);
    });

    Route::prefix('reservation')->group(function () {
        Route::post('/create', [ReservationController::class, 'createReservation']);
        Route::get('/read-complete', [ReservationController::class, 'getReservationsComplete']);
        Route::get('/read-complete-by-room/{roomId}', [ReservationController::class, 'getReservationByRoomIdComplete']);
        Route::get('/read-complete-by-participant/{participantId}', [ReservationController::class, 'getReservationByParticipantIdComplete']);
        Route::patch('/cancel', [ReservationController::class, 'cancelReservation']);
    });
});
