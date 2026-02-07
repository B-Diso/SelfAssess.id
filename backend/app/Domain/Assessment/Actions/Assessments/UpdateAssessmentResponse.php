<?php

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Models\AssessmentResponse;

class UpdateAssessmentResponse
{
    public function handle(AssessmentResponse $assessmentResponse, array $data): AssessmentResponse
    {
        $assessmentResponse->fill($data);
        $assessmentResponse->save();

        return $assessmentResponse;
    }
}
