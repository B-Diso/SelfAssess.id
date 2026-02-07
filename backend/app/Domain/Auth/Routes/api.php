<?php

use App\Domain\Auth\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware(['auth:api'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
});
