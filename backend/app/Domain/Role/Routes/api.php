<?php

use App\Domain\Role\Controllers\AdminRoleController;
use Illuminate\Support\Facades\Route;

// Role management routes authorized via policies
Route::prefix('admin')->middleware(['auth:api'])->group(function () {
    Route::apiResource('/roles', AdminRoleController::class);
    Route::get('/permissions', [AdminRoleController::class, 'permissions']);
});
