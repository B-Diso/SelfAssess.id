<?php

namespace App\Domain\Organization\Controllers;

use App\Domain\Organization\Actions\Organization\UpdateOrganization;
use App\Domain\Organization\Actions\SuperAdmin\CreateOrganization;
use App\Domain\Organization\Actions\SuperAdmin\DeleteOrganization;
use App\Domain\Organization\Models\Organization;
use App\Domain\Organization\Queries\OrganizationQueryService;
use App\Domain\Organization\Requests\CreateOrganizationRequest;
use App\Domain\Organization\Requests\IndexOrganizationRequest;
use App\Domain\Organization\Requests\OrganizationUsersRequest;
use App\Domain\Organization\Requests\UpdateOrganizationRequest;
use App\Domain\Organization\Resources\OrganizationCollection;
use App\Domain\Organization\Resources\OrganizationResource;
use App\Domain\Organization\Resources\OrganizationUserCollection;
use App\Domain\User\Queries\UserQueryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct(
        protected OrganizationQueryService $organizationQueryService,
        protected UserQueryService $userQueryService,
        protected CreateOrganization $createOrganization,
        protected UpdateOrganization $updateOrganization,
        protected DeleteOrganization $deleteOrganization
    ) {
        $this->authorizeResource(Organization::class, 'organization');
    }

    /**
     * List organizations
     * Super Admin sees all, others see own
     */
    public function index(IndexOrganizationRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Organization::class);

        $filters = [
            'search' => $request->get('search'),
            'sortBy' => $request->get('sortBy', 'created_at'),
            'sortOrder' => $request->get('sortOrder', 'desc'),
            'perPage' => $request->get('perPage', 15),
        ];

        // Scope to organization for non-super admins
        if (!$request->user()->isSuperAdmin()) {
            $filters['organization_id'] = $request->user()->organization_id;
        }

        $organizations = $this->organizationQueryService->list($filters);

        return (new OrganizationCollection($organizations))->response();
    }

    /**
     * Create organization (Super Admin only)
     */
    public function store(CreateOrganizationRequest $request): JsonResponse
    {
        $organization = $this->createOrganization->execute($request->validated());

        return response()->json([
            'message' => 'Organization created successfully',
            'data' => new OrganizationResource($organization),
        ], 201);
    }

    /**
     * Get organization detail
     */
    public function show(Organization $organization): JsonResponse
    {
        return response()->json([
            'data' => new OrganizationResource($organization),
        ]);
    }

    /**
     * Update organization
     * Super Admin can update any, Org Admin can update own
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        $updatedOrganization = $this->updateOrganization->execute(
            $organization,
            $request->validated()
        );

        return response()->json([
            'message' => 'Organization updated successfully',
            'data' => new OrganizationResource($updatedOrganization),
        ]);
    }

    /**
     * Soft delete organization (Super Admin only)
     */
    public function destroy(Organization $organization): JsonResponse
    {
        $this->deleteOrganization->execute($organization);

        return response()->json([
            'message' => 'Organization deleted successfully',
        ]);
    }

    /**
     * List users in specific organization
     */
    public function users(OrganizationUsersRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('viewMembers', $organization);

        $filters = [
            'organization_id' => $organization->id,
            'search' => $request->get('search'),
            'perPage' => $request->get('perPage', 15),
            'sortBy' => $request->get('sortBy', 'created_at'),
            'sortOrder' => $request->get('sortOrder', 'desc'),
        ];

        $users = $this->userQueryService->list($filters);

        return (new OrganizationUserCollection($users))->response();
    }
}
