<?php

declare(strict_types=1);

namespace App\Domain\Standard\Controllers;

use App\Domain\Standard\Actions\Standards\CreateStandard;
use App\Domain\Standard\Actions\Standards\DeleteStandard;
use App\Domain\Standard\Actions\Standards\UpdateStandard;
use App\Domain\Standard\Actions\Standards\GetStandardTree;
use App\Domain\Standard\Models\Standard;
use App\Domain\Standard\Queries\StandardQueryService;
use App\Domain\Standard\Requests\Standards\StoreStandardRequest;
use App\Domain\Standard\Requests\Standards\UpdateStandardRequest;
use App\Domain\Standard\Resources\StandardResource;
use App\Domain\Standard\Resources\StandardCollection;
use App\Domain\Standard\Resources\StandardSectionResource;
use App\Domain\Standard\Resources\StandardSectionCollection;
use App\Domain\Standard\Resources\StandardTreeNodeResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StandardController extends Controller
{
    public function __construct(
        protected StandardQueryService $queryService,
        protected CreateStandard $createAction,
        protected UpdateStandard $updateAction,
        protected DeleteStandard $deleteAction,
        protected GetStandardTree $getTreeAction
    ) {
        $this->authorizeResource(Standard::class, 'standard');
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->get('search'),
            'type' => $request->get('type'),
            'isActive' => $request->get('isActive'),
            'perPage' => $request->integer('perPage', 15),
            'sortBy' => $request->get('sortBy', 'updatedAt'),
            'sortOrder' => $request->get('sortOrder', 'desc'),
        ];

        $standards = $this->queryService->list($filters);

        return (new StandardCollection($standards))->response();
    }

    public function store(StoreStandardRequest $request): JsonResponse
    {
        $standard = $this->createAction->execute($request->validated());

        return (new StandardResource($standard))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Standard $standard): JsonResponse
    {
        return (new StandardResource($standard))->response();
    }

    public function update(UpdateStandardRequest $request, Standard $standard): JsonResponse
    {
        $updated = $this->updateAction->execute($standard, $request->validated());

        return (new StandardResource($updated))->response();
    }

    public function destroy(Standard $standard): JsonResponse
    {
        $this->deleteAction->execute($standard);

        return response()->json(null, 204);
    }

    /**
     * Get the full standards tree in a flat response.
     * 
     * GET /api/standards/{standard}/tree
     */
    public function tree(Standard $standard): JsonResponse
    {
        $this->authorize('view', $standard);

        $flatTree = $this->getTreeAction->execute($standard);

        return StandardTreeNodeResource::collection($flatTree)->response();
    }

    /**
     * Get sections for a standard with optional tree structure
     *
     * GET /api/standards/{standard}/sections?tree=true
     */
    public function sections(Request $request, Standard $standard): JsonResponse
    {
        $this->authorize('view', $standard);

        $query = $standard->sections()
            ->with(['children.requirements', 'requirements'])
            ->orderBy('level');

        if ($request->boolean('tree')) {
            $sections = $query->whereNull('parent_id')->get();
            return StandardSectionResource::collection($sections)->response();
        }

        $sections = $query->paginate($request->integer('perPage', 15));
        return (new StandardSectionCollection($sections))->response();
    }
}
