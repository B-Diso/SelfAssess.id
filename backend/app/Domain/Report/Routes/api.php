<?php

declare(strict_types=1);

use App\Domain\Report\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    // Standard Report Summary
    Route::get(
        'reports/standards',
        [ReportController::class, 'standardReport']
    );

    // Response History Report
    Route::get(
        'assessment-responses/{assessment_response}/history',
        [ReportController::class, 'responseHistory']
    );

    // Standard Organizations Report
    Route::get(
        'reports/standards/{standard}/organizations',
        [ReportController::class, 'standardOrganizations']
    )->whereUuid('standard');
});
