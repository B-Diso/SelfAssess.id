<?php

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\Standard\Models\StandardRequirement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateAssessment
{
    public function handle(array $data, string $userId): Assessment
    {
        return DB::transaction(function () use ($data) {
            /** @var Assessment $assessment */
            $assessment = Assessment::create($data);

            // Get all requirements for the standard
            $requirements = StandardRequirement::whereHas('section', function ($query) use ($assessment) {
                $query->where('standard_id', $assessment->standard_id);
            })->get();

            foreach ($requirements as $req) {
                AssessmentResponse::create([
                    'id' => Str::uuid(),
                    'assessment_id' => $assessment->id,
                    'standard_requirement_id' => $req->id,
                    'status' => \App\Domain\Assessment\Enums\AssessmentStatus::ACTIVE->value,
                ]);
            }

            return $assessment;
        });
    }
}
