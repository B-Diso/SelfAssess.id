<?php

namespace App\Domain\User\Controllers;

use App\Domain\User\Actions\Organization\AssignRole;
use App\Domain\User\Actions\Organization\CreateUser;
use App\Domain\User\Actions\Organization\DeleteUser;
use App\Domain\User\Actions\Organization\SyncRole;
use App\Domain\User\Actions\Organization\UpdateUser;
use App\Domain\User\Models\User;
use App\Domain\User\Queries\UserQueryService;
use App\Domain\User\Requests\AssignOrganizationUserRoleRequest;
use App\Domain\User\Requests\StoreOrganizationUserRequest;
use App\Domain\User\Requests\UpdateOrganizationUserRequest;
use App\Domain\User\Resources\OrganizationUserCollection;
use App\Domain\User\Resources\OrganizationUserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationUserController extends Controller
{
    public function __construct(
        protected UserQueryService $userQueryService,
        protected CreateUser $createUser,
        protected UpdateUser $updateUser,
        protected DeleteUser $deleteUser,
        protected AssignRole $assignRole,
        protected SyncRole $syncRole
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $filters = [
            'organization_id' => $request->user()->organization_id,
            'search' => $request->get('search'),
            'role' => $request->get('role'),
            'sortBy' => $request->get('sortBy', 'created_at'),
            'sortOrder' => $request->get('sortOrder', 'desc'),
            'perPage' => $request->get('perPage', 15),
        ];

        $users = $this->userQueryService->list($filters);

        return (new OrganizationUserCollection($users))->response();
    }

    public function store(StoreOrganizationUserRequest $request): JsonResponse
    {
        $user = $this->createUser->execute(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'User created successfully',
            'data' => new OrganizationUserResource($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => new OrganizationUserResource($user->load(['organization', 'roles'])),
        ]);
    }

    public function update(UpdateOrganizationUserRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->updateUser->execute($user, $request->validated());

        return response()->json([
            'message' => 'User updated successfully',
            'data' => new OrganizationUserResource($updatedUser),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->deleteUser->execute($user);

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    public function assignRole(AssignOrganizationUserRoleRequest $request, User $user): JsonResponse
    {
        $this->authorize('assignRole', $user);

        $user = $this->assignRole->execute($user, $request->get('role'));

        return response()->json([
            'message' => 'Role assigned successfully',
            'data' => new OrganizationUserResource($user),
        ]);
    }

    public function updateRole(AssignOrganizationUserRoleRequest $request, User $user): JsonResponse
    {
        $this->authorize('assignRole', $user);

        $user = $this->syncRole->execute($user, $request->get('role'));

        return response()->json([
            'message' => 'Role updated successfully',
            'data' => new OrganizationUserResource($user),
        ]);
    }
}
