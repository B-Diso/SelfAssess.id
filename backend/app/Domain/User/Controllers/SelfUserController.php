<?php

namespace App\Domain\User\Controllers;

use App\Domain\User\Actions\Self\UpdatePassword;
use App\Domain\User\Actions\Self\UpdateProfile;
use App\Domain\User\Requests\UpdateSelfPasswordRequest;
use App\Domain\User\Requests\UpdateSelfProfileRequest;
use App\Domain\User\Resources\AuthenticatedUserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SelfUserController extends Controller
{
    public function __construct(
        protected UpdateProfile $updateProfile,
        protected UpdatePassword $updatePassword
    ) {}

    public function updateProfile(UpdateSelfProfileRequest $request): JsonResponse
    {
        $updatedUser = $this->updateProfile->execute($request->user(), $request->validated());

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => new AuthenticatedUserResource($updatedUser),
        ]);
    }

    public function updatePassword(UpdateSelfPasswordRequest $request): JsonResponse
    {
        $this->updatePassword->execute($request->user(), $request->validated());

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }
}
