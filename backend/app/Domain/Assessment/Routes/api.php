<?php

declare(strict_types=1);

use App\Domain\Assessment\Controllers\AssessmentController;
use App\Domain\Assessment\Controllers\AssessmentActionPlanController;
use App\Domain\Assessment\Controllers\AssessmentResponseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('assessments', AssessmentController::class);
    Route::apiResource('assessment-responses', AssessmentResponseController::class)->only(['update']);
    Route::apiResource('assessment-action-plans', AssessmentActionPlanController::class);

    // Unified Workflow Transitions
    Route::post('assessments/{assessment}/workflow', [AssessmentController::class, 'workflow'])
        ->name('assessments.workflow');

    Route::post('assessment-responses/{assessment_response}/workflow', [AssessmentResponseController::class, 'workflow'])
        ->name('assessment-responses.workflow');
});
