<?php

namespace App\Domain\Role\Controllers;

use App\Domain\Role\Actions\SuperAdmin\CreateRole;
use App\Domain\Role\Actions\SuperAdmin\DeleteRole;
use App\Domain\Role\Actions\SuperAdmin\UpdateRole;
use App\Domain\Role\Queries\RoleQueryService;
use App\Domain\Role\Requests\StoreRoleRequest;
use App\Domain\Role\Requests\UpdateRoleRequest;
use App\Domain\Role\Resources\PermissionCollection;
use App\Domain\Role\Resources\PermissionResource;
use App\Domain\Role\Resources\RoleCollection;
use App\Domain\Role\Resources\RoleResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    public function __construct(
        protected RoleQueryService $roleQueryService,
        protected CreateRole $createRole,
        protected UpdateRole $updateRole,
        protected DeleteRole $deleteRole
    ) {
        $this->authorizeResource(Role::class, 'role');
    }

    /**
     * List all roles
     * Super Admin sees all, Org Admin sees organization-level roles only
     */

    public function index(Request $request): JsonResponse
    {
        $roles = $this->roleQueryService->getRolesForUser($request->user());

        return (new RoleCollection($roles))->response();
    }

    /**
     * Get role detail with permissions
     */
    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'data' => new RoleResource($role->load('permissions')),
        ]);
    }

    /**
     * Create custom role (Super Admin only)
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->createRole->execute($request->validated());

        return response()->json([
            'message' => 'Role created successfully',
            'data' => new RoleResource($role),
        ], 201);
    }

    /**
     * Update role permissions (Super Admin only)
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $updatedRole = $this->updateRole->execute($role, $request->validated());

        return response()->json([
            'message' => 'Role updated successfully',
            'data' => new RoleResource($updatedRole),
        ]);
    }

    /**
     * Delete custom role (Super Admin only)
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->deleteRole->execute($role);

        return response()->json([
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * List all permissions (Super Admin only)
     */
    public function permissions(): JsonResponse
    {
        $this->authorize('viewPermissions', Role::class);

        $permissions = $this->roleQueryService->getAllPermissions();

        return (new PermissionCollection($permissions))->response();
    }
}
