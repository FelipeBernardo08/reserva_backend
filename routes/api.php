<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ParticipantController;
use Illuminate\Http\Request;
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
});
