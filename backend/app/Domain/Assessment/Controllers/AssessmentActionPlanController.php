<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Controllers;

use App\Domain\Assessment\Actions\ActionPlans\CreateActionPlan;
use App\Domain\Assessment\Actions\ActionPlans\DeleteActionPlan;
use App\Domain\Assessment\Actions\ActionPlans\UpdateActionPlan;
use App\Domain\Assessment\Models\AssessmentActionPlan;
use App\Domain\Assessment\Requests\ActionPlans\StoreActionPlanRequest;
use App\Domain\Assessment\Requests\ActionPlans\UpdateActionPlanRequest;
use App\Domain\Assessment\Resources\AssessmentActionPlanCollection;
use App\Domain\Assessment\Resources\AssessmentActionPlanResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentActionPlanController extends Controller
{
    public function __construct(
        protected CreateActionPlan $createActionPlan,
        protected UpdateActionPlan $updateActionPlan,
        protected DeleteActionPlan $deleteActionPlan
    ) {}

    public function index(Request $request): AssessmentActionPlanCollection
    {
        $user = $request->user();
        $query = AssessmentActionPlan::query()
            ->whereHas('assessment', function ($q) use ($user) {
                if (! $user->isSuperAdmin()) {
                    $q->where('organization_id', $user->organization_id);
                }
            })
            ->orderByDesc('created_at');

        if ($request->filled('assessmentId')) {
            $query->where('assessment_id', $request->get('assessmentId'));
        }

        if ($request->filled('assessmentResponseId')) {
            $query->where('assessment_response_id', $request->get('assessmentResponseId'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        return new AssessmentActionPlanCollection($query->paginate($request->integer('perPage', 15)));
    }

    public function show(AssessmentActionPlan $assessmentActionPlan): AssessmentActionPlanResource
    {
        $this->authorize('view', $assessmentActionPlan->assessment);

        return new AssessmentActionPlanResource($assessmentActionPlan);
    }

    public function store(StoreActionPlanRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Authorization check - use manageActionPlan policy on response
        // Action plans can only be created when response status is 'active'
        $response = \App\Domain\Assessment\Models\AssessmentResponse::findOrFail($validated['assessment_response_id']);
        $this->authorize('manageActionPlan', $response);

        $plan = $this->createActionPlan->handle($validated);

        return (new AssessmentActionPlanResource($plan))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateActionPlanRequest $request,
        AssessmentActionPlan $assessmentActionPlan
    ): AssessmentActionPlanResource {
        $plan = $this->updateActionPlan->handle($assessmentActionPlan, $request->validated());

        return new AssessmentActionPlanResource($plan);
    }

    public function destroy(AssessmentActionPlan $assessmentActionPlan): JsonResponse
    {
        // Authorization check - use manageActionPlan policy on response
        $this->authorize('manageActionPlan', $assessmentActionPlan->assessmentResponse);
        $this->deleteActionPlan->handle($assessmentActionPlan);

        return response()->json(['message' => 'Action plan deleted successfully']);
    }
}
