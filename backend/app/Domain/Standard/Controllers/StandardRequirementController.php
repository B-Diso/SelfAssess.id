<?php

declare(strict_types=1);

namespace App\Domain\Standard\Controllers;

use App\Domain\Standard\Actions\Requirements\CreateRequirement;
use App\Domain\Standard\Actions\Requirements\DeleteRequirement;
use App\Domain\Standard\Actions\Requirements\UpdateRequirement;
use App\Domain\Standard\Models\StandardRequirement;
use App\Domain\Standard\Requests\Requirements\StoreStandardRequirementRequest;
use App\Domain\Standard\Requests\Requirements\UpdateStandardRequirementRequest;
use App\Domain\Standard\Resources\StandardRequirementCollection;
use App\Domain\Standard\Resources\StandardRequirementResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StandardRequirementController extends Controller
{
    public function __construct(
        protected CreateRequirement $createAction,
        protected UpdateRequirement $updateAction,
        protected DeleteRequirement $deleteAction
    ) {
        $this->authorizeResource(StandardRequirement::class, 'requirement');
    }

    public function index(Request $request): StandardRequirementCollection
    {
        $query = StandardRequirement::query();

        if ($request->filled('standard_section_id')) {
            $query->where('standard_section_id', $request->get('standard_section_id'));
        }

        $requirements = $query->paginate($request->integer('perPage', 15));

        return new StandardRequirementCollection($requirements);
    }

    public function store(StoreStandardRequirementRequest $request): JsonResponse
    {
        $requirement = $this->createAction->execute($request->validated());
        return (new StandardRequirementResource($requirement))
            ->response()
            ->setStatusCode(201);
    }

    public function show(StandardRequirement $requirement): StandardRequirementResource
    {
        return new StandardRequirementResource($requirement);
    }

    public function update(UpdateStandardRequirementRequest $request, StandardRequirement $requirement): StandardRequirementResource
    {
        $updated = $this->updateAction->execute($requirement, $request->validated());
        return new StandardRequirementResource($updated);
    }

    public function destroy(StandardRequirement $requirement): JsonResponse
    {
        $this->deleteAction->execute($requirement);
        return response()->json(null, 204);
    }
}
