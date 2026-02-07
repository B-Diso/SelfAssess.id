<?php

declare(strict_types=1);

use App\Domain\Attachment\Controllers\AttachmentController;
use App\Domain\Attachment\Controllers\AttachmentBridgeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');

    Route::apiResource('attachments', AttachmentController::class)->only(['index', 'store', 'show', 'destroy']);

    // Bridge Endpoints for linking attachments to assessment responses (Evidence)
    Route::prefix('attachment-links')->group(function () {
        Route::get('assessment-response/{assessment_response}', [AttachmentBridgeController::class, 'getAssessmentResponseAttachments'])
            ->name('attachments.bridge.assessment.index');
        Route::post('assessment-response', [AttachmentBridgeController::class, 'linkToAssessmentResponse'])
            ->name('attachments.bridge.assessment.link');
        Route::post('assessment-response/upload', [AttachmentBridgeController::class, 'uploadToAssessmentResponse'])
            ->name('attachments.bridge.assessment.upload');
        Route::delete('assessment-response/{assessment_response}/{attachment}', [AttachmentBridgeController::class, 'unlinkFromAssessmentResponse'])
            ->name('attachments.bridge.assessment.unlink');
    });
});
