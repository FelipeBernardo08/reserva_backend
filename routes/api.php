<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationParticipantController;
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

    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('participant')->group(function () {
        Route::post('/create', [ParticipantController::class, 'createParticipant']);
        Route::get('/read-all', [ParticipantController::class, 'readAllParticipants']);
        Route::get('/read-by-id/{id}', [ParticipantController::class, 'readParticipantById']);
    });

    Route::prefix('room')->group(function () {
        Route::post('/create', [RoomController::class, 'createRoom']);
        Route::get('/read-all', [RoomController::class, 'readAllRooms']);
        Route::get('/read-by-id/{id}', [RoomController::class, 'readRoomById']);
    });

    Route::prefix('reservation')->group(function () {
        Route::post('/create', [ReservationController::class, 'createReservation']);
        Route::get('/read-complete', [ReservationController::class, 'getReservationsComplete']);
        Route::get('/read-complete-by-id/{id}', [ReservationController::class, 'getReservationsCompleteById']);
        Route::get('/read-complete-by-room/{roomId}', [ReservationController::class, 'getReservationByRoomIdComplete']);
        Route::get('/read-complete-by-participant/{participantId}', [ReservationController::class, 'getReservationByParticipantIdComplete']);
        Route::patch('/cancel', [ReservationController::class, 'cancelReservation']);
    });

    Route::prefix('reservation-participant')->group(function () {
        Route::put('/update', [ReservationParticipantController::class, 'updateReservationParticipant']);
    });
});
