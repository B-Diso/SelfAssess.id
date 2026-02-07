<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Actions\LoginAction;
use App\Domain\Auth\Actions\RefreshTokenAction;
use App\Domain\Auth\Requests\LoginRequest;
use App\Domain\Auth\Requests\UpdateProfileRequest;
use App\Domain\Auth\Resources\AuthUserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginAction $loginAction,
        private readonly RefreshTokenAction $refreshTokenAction
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->loginAction->execute(
            $request->input('email'),
            $request->input('password'),
            $request->boolean('rememberMe', true)
        );

        return response()->json([
            'accessToken' => $result['token'],
            'tokenType' => 'bearer',
            'expiresIn' => $result['expiresIn'],
            'rememberMe' => $result['rememberMe'],
            'sessionDuration' => $result['sessionDuration'],
            'user' => new AuthUserResource($result['user']),
        ])->withCookie($result['refreshTokenCookie']);
    }

    public function logout(): JsonResponse
    {
        auth()->logout();

        // Clear refresh_token cookie
        $cookie = cookie('refresh_token', '', -1, null, null, false, true, false, 'Strict');

        return response()->json(['message' => 'Successfully logged out'])->withCookie($cookie);
    }

    public function refresh(Request $request): JsonResponse
    {
        // Refresh token is automatically read from cookie in RefreshTokenAction
        $result = $this->refreshTokenAction->execute();
        
        return response()->json([
            'accessToken' => $result['token'],
            'tokenType' => 'bearer',
            'expiresIn' => $result['expiresIn'],
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json(new AuthUserResource(auth()->user()));
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        $user->update($data);
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new AuthUserResource($user->fresh()),
        ]);
    }
}
