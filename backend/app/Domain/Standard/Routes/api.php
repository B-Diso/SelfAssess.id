<?php

declare(strict_types=1);

use App\Domain\Standard\Controllers\StandardController;
use App\Domain\Standard\Controllers\StandardSectionController;
use App\Domain\Standard\Controllers\StandardRequirementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    // Nested standards resources
    Route::get('standards/{standard}/tree', [StandardController::class, 'tree'])
        ->name('standards.tree');
    Route::get('standards/{standard}/sections', [StandardController::class, 'sections'])
        ->name('standards.sections');

    // CRUD Resources
    Route::apiResource('standards', StandardController::class);
    Route::apiResource('standards.sections', StandardSectionController::class)->shallow();
    Route::apiResource('standards.requirements', StandardRequirementController::class)->shallow();
});
