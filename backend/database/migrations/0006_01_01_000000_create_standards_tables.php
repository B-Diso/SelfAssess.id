<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Schema Hierarchy:
         * standards (Top level standard, e.g., GIAS 2024)
         * └── standard_elements (Core principles within domains)
         *     └── standard_requirements (Mandatory requirements)
         * └── standard_domains (Broad categories/sections)
         *     └── standard_elements (Core principles within domains)
         *         └── standard_requirements (Mandatory requirements)
         *
         * So this table can be flexible enough to create standards tree structure of any standard.
         */

        /*
        1. Only superadmin can create standards and their details.
        2. All user can see standards and their details if standards is active.
        3. Only siperadmin can update standards and their details.
        4. Only superadmin can delete standards and their details.
        5. there is endpoint with /tree that show full standards tree in flat response (not nested).
        */

        // 1. Standards Table
        Schema::create('standards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "Global Internal Audit Standards"
            $table->string('version'); // e.g., "2024"

            /*
             * Type: Classification of the standard's origin and scope
             *
             * - 'internal': Organization-specific standards, policies, and internal frameworks
             *
             * - 'regulatory': Government or regulatory body mandated standards
             *   Examples: SOX, GDPR, HIPAA, Basel III, MiFID II
             *
             * - 'standard': International standards and certifications
             *   Examples: ISO 9001, ISO 27001, ISO 31000, ISO 20000
             *
             * - 'bestPractice': Industry best practices and body of knowledge frameworks
             *   Examples: ITIL, COBIT, TOGAF, PMBOK, DAMA-BOK, BABOK, NIST CSF, COSO, CIS Controls, IPPF
             *
             * - 'other': Other standards not categorized above
             */
            $table->string('type')->default('internal');

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Standard Sections (Recursive: Domain -> Element -> Requirement -> etc.)
        // if standard had multiple type of sections and not only domain. this table is for creating the tree structure of the standard sections
        Schema::create('standard_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('standard_id')->constrained()->cascadeOnDelete();
            $table->uuid('parent_id')->nullable(); // for recursive relationship with self
            $table->string('type')->nullable(); // e.g., 'domain', 'element' (now optional)
            $table->string('code', 50); // e.g., "Domain 1", "1.1"
            $table->string('title', 255);
            $table->integer('level')->default(0); // hierarchy depth (0 for root)
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['standard_id', 'parent_id']);
            $table->index('level');
        });

        // 3. Standard Requirements (Link Table)
        Schema::create('standard_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('standard_section_id')->constrained('standard_sections')->cascadeOnDelete();
            $table->string('display_code'); // e.g., "Standard 1.1"
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->text('evidence_hint')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['standard_section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_requirements');
        Schema::dropIfExists('standard_sections');
        Schema::dropIfExists('standards');
    }
};
