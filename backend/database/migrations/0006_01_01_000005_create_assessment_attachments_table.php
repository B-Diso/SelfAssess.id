<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This table handles the Many-to-Many relationship between Assessment Responses and Attachments.
     * This allows a single document from the "Document Center" to be used as evidence 
     * for multiple requirements or across different assessments.
     */
    public function up(): void
    {
        Schema::create('assessment_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('assessment_response_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('attachment_id')->constrained()->cascadeOnDelete();
            
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            // Indexes for faster lookups
            $table->index(['assessment_id', 'assessment_response_id']);
            $table->index(['assessment_response_id', 'attachment_id']);
            
            // Prevent duplicate links for the same response
            $table->unique(['assessment_response_id', 'attachment_id'], 'assess_resp_attach_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_attachments');
    }
};
