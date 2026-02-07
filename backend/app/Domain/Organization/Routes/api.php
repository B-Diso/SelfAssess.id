<?php

use App\Domain\Organization\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('organizations', OrganizationController::class);
    Route::get('organizations/{organization}/users', [OrganizationController::class, 'users']);
});
