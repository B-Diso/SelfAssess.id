<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {


        // 1. Assessments Table
        Schema::create('assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('standard_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Q1 2025 Audit"
            $table->string('period_value')->nullable(); // e.g., "April 2025", "Q1 2025", "Semester 1 2025"
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 50)->default('draft'); // draft, active, pending_review, reviewed, pending_finish, finished, cancelled (VARCHAR with app-layer validation)
            /*
            Assessment Workflow:
            Draft:           Initial state, all input is editable
            Active:          Assessment is in progress, all input is editable
            Pending Review:  Assessment submitted for review, input is not editable (locked)
            Reviewed:        Assessment has been reviewed by reviewer, input is not editable (locked)
            Pending Finish:  Organization user needs to take final action after review
            Finished:        Assessment is completed and archived, input is not editable (locked)
            Cancelled:       Assessment is cancelled, input is not editable (locked) and can be activated again
            */
            $table->index('status'); // Add index for performance
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Assessment Responses
        Schema::create('assessment_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('standard_requirement_id')->constrained()->cascadeOnDelete();
            $table->string('status', 50)->default('active'); // active, pending_review, reviewed (VARCHAR with app-layer validation)
            $table->index('status'); // Add index for performance
            /*
            Requirement Response Workflow:
            Active:          Initial state, organization user can fill/edit compliance status
            Pending Review:  Organization user finished filling, waiting for reviewer review
            Reviewed:        Reviewer has reviewed and approved the requirement response
            */
            $table->text('comments')->nullable();
            $table->string('compliance_status', 20)->default('non_compliant'); // non_compliant, partially_compliant, fully_compliant, not_applicable. Limit at requests
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['assessment_id', 'standard_requirement_id'], 'assess_resp_unique');
        });

        Schema::create('assessment_action_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('assessment_response_id')->constrained('assessment_responses')->cascadeOnDelete();
            $table->string('title');
            $table->text('action_plan')->nullable();
            $table->date('due_date')->nullable();
            $table->string('pic')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Workflow Logs
        Schema::create('assessment_workflow_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('loggable'); // Assessment or AssessmentResponse
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_workflow_logs');
        Schema::dropIfExists('assessment_action_plans');
        Schema::dropIfExists('assessment_responses');
        Schema::dropIfExists('assessments');
    }
};
