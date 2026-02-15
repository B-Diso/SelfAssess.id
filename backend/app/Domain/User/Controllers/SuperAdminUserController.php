<?php

namespace App\Domain\User\Controllers;

use App\Domain\User\Actions\SuperAdmin\CreateUser;
use App\Domain\User\Actions\SuperAdmin\DeleteUser;
use App\Domain\User\Actions\SuperAdmin\TransferUser;
use App\Domain\User\Actions\SuperAdmin\UpdateUser;
use App\Domain\User\Models\User;
use App\Domain\User\Queries\UserQueryService;
use App\Domain\User\Requests\StoreSuperAdminUserRequest;
use App\Domain\User\Requests\TransferUserBetweenOrganizationsRequest;
use App\Domain\User\Requests\UpdateSuperAdminUserRequest;
use App\Domain\User\Resources\SuperAdminUserCollection;
use App\Domain\User\Resources\SuperAdminUserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuperAdminUserController extends Controller
{
    public function __construct(
        protected UserQueryService $userQueryService,
        protected CreateUser $createUser,
        protected UpdateUser $updateUser,
        protected DeleteUser $deleteUser,
        protected TransferUser $transferUser
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        abort_unless($request->user()?->isSuperAdmin(), 403, 'This action requires Super Admin access');

        $filters = [
            'search' => $request->get('search'),
            'organization_id' => $request->get('organizationId'),
            'role' => $request->get('role'),
            'sortBy' => $request->get('sortBy', 'created_at'),
            'sortOrder' => $request->get('sortOrder', 'desc'),
            'perPage' => $request->get('perPage', 15),
        ];

        if (! $request->user()->isSuperAdmin()) {
            $filters['organization_id'] = $request->user()->organization_id;
        }

        $users = $this->userQueryService->list($filters);

        // Using Resource Collection with CleansPaginationResponse trait
        // This will automatically remove 'links', 'meta.path', and 'meta.to'
        return (new SuperAdminUserCollection($users))->response();
    }

    public function store(StoreSuperAdminUserRequest $request): JsonResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'This action requires Super Admin access');
        $user = $this->createUser->execute($request->validated(), $request->user());

        return response()->json([
            'message' => 'User created successfully',
            'data' => new SuperAdminUserResource($user),
        ], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);
        abort_unless($request->user()?->isSuperAdmin(), 403, 'This action requires Super Admin access');

        return response()->json([
            'data' => new SuperAdminUserResource($user->load(['organization', 'roles'])),
        ]);
    }

    public function update(UpdateSuperAdminUserRequest $request, User $user): JsonResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'This action requires Super Admin access');
        $updatedUser = $this->updateUser->execute($user, $request->validated());

        return response()->json([
            'message' => 'User updated successfully',
            'data' => new SuperAdminUserResource($updatedUser),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'This action requires Super Admin access');
        $this->deleteUser->execute($user);

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    public function transfer(TransferUserBetweenOrganizationsRequest $request): JsonResponse
    {
        $this->authorize('transfer', User::class);
        abort_unless($request->user()?->isSuperAdmin(), 403, 'This action requires Super Admin access');

        $data = $request->validated();
        $user = User::findOrFail($data['userId']);

        $transferredUser = $this->transferUser->execute(
            $user,
            $data['targetOrganizationId'],
            $data['reason'] ?? null,
            $request->user()
        );

        return response()->json([
            'message' => 'User transferred successfully',
            'data' => new SuperAdminUserResource($transferredUser),
        ]);
    }
}
