<?php

declare(strict_types=1);

use App\Domain\Dashboard\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->prefix('dashboard')->group(function () {
    Route::get('/statistics', [DashboardController::class, 'statistics']);
    Route::get('/activities', [DashboardController::class, 'activities']);
    Route::get('/assessments', [DashboardController::class, 'assessments']);
    Route::get('/compliance-trend', [DashboardController::class, 'complianceTrend']);
    Route::get('/action-plans', [DashboardController::class, 'actionPlans']);
});
