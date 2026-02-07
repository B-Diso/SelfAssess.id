<?php

namespace Database\Seeders;

use App\Domain\Attachment\Models\Attachment;
use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\Organization\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AttachmentSeeder extends Seeder
{
    /**
     * Attachment categories
     */
    private array $categories = [
        'evidence',
        'policy',
        'procedure',
        'report',
        'training',
        'audit',
    ];

    /**
     * Dummy file descriptions
     */
    private array $descriptionTemplates = [
        'Supporting documentation for {category} requirement',
        '{category} record demonstrating compliance',
        'Official {category} documentation',
        '{category} evidence of implementation',
        'Verified {category} documentation',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📎 Seeding attachments...');
        $this->command->newLine();

        // Get source PDF files from seeders directory
        $sourceFiles = [
            database_path('seeders/pdf-sample_0.pdf'),
            database_path('seeders/dummy-pdf_2.pdf'),
        ];

        // Verify source files exist
        $existingFiles = array_filter($sourceFiles, fn($file) => file_exists($file));

        if (empty($existingFiles)) {
            $this->command->warn('⚠️  No PDF files found in database/seeders/ directory.');
            $this->command->warn('   Please add pdf-sample_0.pdf and dummy-pdf_2.pdf to continue.');
            return;
        }

        $organizations = Organization::where('name', '!=', config('organization.master.name'))->get();

        if ($organizations->isEmpty()) {
            $this->command->warn('⚠️  No organizations found. Please run OrganizationSeeder first.');
            return;
        }

        $totalAttachments = 0;
        $totalLinks = 0;

        foreach ($organizations as $org) {
            $this->command->info("Organization: {$org->name}");

            // Create 3-5 attachments per organization
            $attachmentCount = rand(3, 5);

            for ($i = 0; $i < $attachmentCount; $i++) {
                $attachment = $this->createAttachment($org, $existingFiles[array_rand($existingFiles)]);
                $totalAttachments++;

                // Link to 2-5 responses
                $linkCount = $this->linkToResponses($attachment, $org);
                $totalLinks += $linkCount;

                $this->command->info("  ✓ Created: {$attachment->name} (linked to {$linkCount} responses)");
            }
        }

        $this->command->newLine();
        $this->command->info("✅ Total {$totalAttachments} attachments created with {$totalLinks} response links!");
    }

    /**
     * Create attachment record and copy file
     */
    private function createAttachment(Organization $org, string $sourceFile): Attachment
    {
        $category = $this->categories[array_rand($this->categories)];
        $filename = basename($sourceFile);

        // Generate description
        $descriptionTemplate = $this->descriptionTemplates[array_rand($this->descriptionTemplates)];
        $description = str_replace('{category}', $category, $descriptionTemplate);

        // Copy file to storage
        $storagePath = $this->copyFileToStorage($sourceFile, $org->id, $category, $filename);

        // Get file info
        $mimeType = mime_content_type($sourceFile);
        $fileSize = filesize($sourceFile);

        // Get random user from organization
        $userId = $this->getRandomUserId($org->id);

        $attachment = Attachment::create([
            'organization_id' => $org->id,
            'name' => $filename,
            'mime_type' => $mimeType,
            'size' => $fileSize,
            'path' => $storagePath,
            'disk' => 'local',
            'category' => $category,
            'description' => $description,
            'created_by_id' => $userId,
            'updated_by_id' => $userId,
        ]);

        return $attachment;
    }

    /**
     * Copy file to storage directory
     */
    private function copyFileToStorage(string $sourceFile, string $organizationId, string $category, string $filename): string
    {
        // Generate secure filename
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $secureFilename = \Illuminate\Support\Str::random(40) . '.' . $extension;

        // Create storage path
        $storagePath = "attachments/{$organizationId}/{$category}/{$secureFilename}";

        // Ensure directory exists
        $fullPath = Storage::disk('local')->path($storagePath);
        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Copy file
        copy($sourceFile, $fullPath);

        return $storagePath;
    }

    /**
     * Link attachment to assessment responses
     */
    private function linkToResponses(Attachment $attachment, Organization $org): int
    {
        // Get random responses from this organization
        $responses = AssessmentResponse::whereHas('assessment', function ($query) use ($org) {
            $query->where('organization_id', $org->id);
        })
        ->where('status', '!=', 'active') // Only link to reviewed/pending_review responses
        ->inRandomOrder()
        ->limit(rand(2, 5))
        ->get();

        if ($responses->isEmpty()) {
            return 0;
        }

        // Link attachment to responses
        foreach ($responses as $response) {
            DB::table('assessment_attachments')->insert([
                'id' => \Illuminate\Support\Str::uuid7()->toString(),
                'assessment_id' => $response->assessment_id,
                'assessment_response_id' => $response->id,
                'attachment_id' => $attachment->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $responses->count();
    }

    /**
     * Get random user ID from organization
     */
    private function getRandomUserId(string $organizationId): ?string
    {
        return \App\Domain\User\Models\User::where('organization_id', $organizationId)
            ->inRandomOrder()
            ->first()?->id;
    }
}
