<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This table is for polymorphic relation between standard_requirement and attachments
     */
    public function up(): void
    {
        Schema::create('standard_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('standard_id')->constrained('standards')->cascadeOnDelete();
            $table->foreignUuid('standard_requirement_id')->constrained('standard_requirements')->cascadeOnDelete();
            $table->foreignUuid('attachment_id')->constrained('attachments')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->index(['standard_id', 'standard_requirement_id', 'attachment_id']);
            $table->index(['standard_id', 'standard_requirement_id']);
            $table->index(['standard_id', 'attachment_id']);
            $table->index(['standard_requirement_id', 'attachment_id']);
            $table->unique(['standard_requirement_id', 'attachment_id'], 'std_req_attachment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_attachments');
    }
};
