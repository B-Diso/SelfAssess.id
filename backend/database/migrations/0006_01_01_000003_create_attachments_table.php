<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            // Primary key - UUID v7
            $table->uuid('id')->primary();

            // File metadata
            $table->uuid('organization_id');
            $table->string('name');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->string('path');
            $table->string('disk')->default('local');
            $table->string('category')->nullable(); // only documentation, evidence, reference_material for now.
            $table->text('description')->nullable();

            // Audit trail
            $table->uuid('created_by_id')->nullable();
            $table->uuid('updated_by_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');

            // Indexes
            $table->index('category');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
