<?php

declare(strict_types=1);

namespace App\Domain\Standard\Controllers;

use App\Domain\Standard\Actions\Sections\CreateSection;
use App\Domain\Standard\Actions\Sections\DeleteSection;
use App\Domain\Standard\Actions\Sections\UpdateSection;
use App\Domain\Standard\Models\StandardSection;
use App\Domain\Standard\Requests\Sections\StoreStandardSectionRequest;
use App\Domain\Standard\Requests\Sections\UpdateStandardSectionRequest;
use App\Domain\Standard\Resources\StandardSectionResource;
use App\Domain\Standard\Resources\StandardSectionCollection;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StandardSectionController extends Controller
{
    public function __construct(
        protected CreateSection $createAction,
        protected UpdateSection $updateAction,
        protected DeleteSection $deleteAction
    ) {
        $this->authorizeResource(StandardSection::class, 'section');
    }

    public function index(Request $request): StandardSectionCollection
    {
        $query = StandardSection::query()->orderBy('level');

        if ($request->filled('standard_id')) {
            $query->where('standard_id', $request->standard_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $sections = $query->paginate($request->integer('perPage', 15));
        return new StandardSectionCollection($sections);
    }

    public function store(StoreStandardSectionRequest $request): JsonResponse
    {
        $section = $this->createAction->execute($request->validated());
        return (new StandardSectionResource($section))
            ->response()
            ->setStatusCode(201);
    }

    public function show(StandardSection $section): StandardSectionResource
    {
        return new StandardSectionResource($section);
    }

    public function update(UpdateStandardSectionRequest $request, StandardSection $section): StandardSectionResource
    {
        $updated = $this->updateAction->execute($section, $request->validated());
        return new StandardSectionResource($updated);
    }

    public function destroy(StandardSection $section): JsonResponse
    {
        $this->deleteAction->execute($section);
        return response()->json(null, 204);
    }
}
