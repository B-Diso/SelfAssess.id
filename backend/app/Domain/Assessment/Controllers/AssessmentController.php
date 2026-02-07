<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Controllers;

use App\Domain\Assessment\Actions\Assessments\CreateAssessment;
use App\Domain\Assessment\Actions\Assessments\TransitionAssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Requests\Assessments\StoreAssessmentRequest;
use App\Domain\Assessment\Requests\Assessments\UpdateAssessmentRequest;
use App\Domain\Assessment\Requests\Assessments\AssessmentWorkflowRequest;
use App\Domain\Assessment\Resources\AssessmentCollection;
use App\Domain\Assessment\Resources\AssessmentResource;
use App\Domain\Assessment\Resources\AssessmentWorkflowResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function __construct(
        protected CreateAssessment $createAssessment,
        protected TransitionAssessmentStatus $transitionAssessmentStatus
    ) {
        $this->authorizeResource(Assessment::class, 'assessment');
    }

    public function index(Request $request): AssessmentCollection
    {
        $query = Assessment::with(['organization', 'standard', 'responses'])
            ->withCount([
                'responses',
                'responses as completed_responses_count' => function ($query) {
                    $query->where('status', 'reviewed');
                },
                'responses as pending_review_responses_count' => function ($query) {
                    $query->where('status', 'pending_review');
                }
            ])
            ->orderByDesc('created_at');
        if (!$request->user()->isSuperAdmin()) {
            $query->where('organization_id', $request->user()->organization_id);
        } elseif ($request->filled('organization_id')) {
            $query->where('organization_id', $request->get('organization_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Case-insensitive search across assessment name and standard name
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhereHas('standard', function ($standardQuery) use ($searchTerm) {
                      $standardQuery->where('name', 'ILIKE', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('organization', function ($orgQuery) use ($searchTerm) {
                      $orgQuery->where('name', 'ILIKE', '%' . $searchTerm . '%');
                  });
            });
        }

        $assessments = $query->paginate($request->integer('perPage', 15));

        return new AssessmentCollection($assessments);
    }

    public function store(StoreAssessmentRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        
        if (!$user->isSuperAdmin()) {
            $data['organization_id'] = $user->organization_id;
        } else {
            $data['organization_id'] = $data['organization_id'] ?? $user->organization_id;
        }

        $assessment = $this->createAssessment->handle($data, (string) $user->id);
        $assessment->load('responses');

        return (new AssessmentResource($assessment))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Assessment $assessment): AssessmentResource
    {
        $assessment->load(['responses.requirement', 'organization'])
            ->loadCount([
                'responses',
                'responses as completed_responses_count' => function ($query) {
                    $query->where('status', 'reviewed');
                },
                'responses as pending_review_responses_count' => function ($query) {
                    $query->where('status', 'pending_review');
                }
            ]);
        return new AssessmentResource($assessment);
    }

    public function update(UpdateAssessmentRequest $request, Assessment $assessment): AssessmentResource
    {
        $assessment->fill($request->validated());
        $assessment->save();

        return new AssessmentResource($assessment);
    }

    public function destroy(Assessment $assessment): JsonResponse
    {
        $assessment->delete();
        return response()->json(['message' => 'Assessment deleted successfully']);
    }

    /**
     * Process assessment workflow transition.
     */
    public function workflow(AssessmentWorkflowRequest $request, Assessment $assessment): AssessmentWorkflowResponse
    {
        $toStatus = $request->validated('status');
        
        // Pass the target status as an additional parameter to the policy
        $this->authorize('transition', [$assessment, $toStatus]);

        $assessment = $this->transitionAssessmentStatus->handle(
            $assessment,
            $toStatus,
            $request->validated('note'),
            $request->user()
        );

        return new AssessmentWorkflowResponse($assessment);
    }
}
