<?php

use App\Domain\User\Controllers\OrganizationUserController;
use App\Domain\User\Controllers\SelfUserController;
use App\Domain\User\Controllers\SuperAdminUserController;
use Illuminate\Support\Facades\Route;

// Organization-scoped user endpoints authorized via policies
Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('/users', OrganizationUserController::class);
    Route::post('/users/{user}/roles', [OrganizationUserController::class, 'assignRole']);
    Route::put('/users/{user}/roles', [OrganizationUserController::class, 'updateRole']);
    Route::patch('/me/profile', [SelfUserController::class, 'updateProfile']);
    Route::patch('/me/password', [SelfUserController::class, 'updatePassword']);
});

// Admin endpoints authorized via policies
Route::prefix('admin')->middleware(['auth:api'])->group(function () {
    Route::post('/users/transfer', [SuperAdminUserController::class, 'transfer']);
    Route::apiResource('/users', SuperAdminUserController::class);
});
