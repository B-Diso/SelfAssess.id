<?php

namespace App\Domain\Auth\Actions;

use App\Domain\User\Models\User;
use App\Exceptions\Domain\InvalidCredentialsException;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieFactory;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LoginAction
{
    private const REMEMBER_ME_TTL_MINUTES = 43200; // 30 days
    private const SESSION_TTL_MINUTES = 480;       // 8 hours

    public function __construct(
        private readonly CookieFactory $cookieJar
    ) {}

    public function execute(string $email, string $password, bool $rememberMe = false): array
    {
        $credentials = ['email' => $email, 'password' => $password];

        if (!$token = auth()->attempt($credentials)) {
            throw new InvalidCredentialsException();
        }

        /** @var User $user */
        $user = auth()->user();
        $user->update(['last_login_at' => now()]);

        // Calculate refresh token TTL based on rememberMe
        $refreshTtlMinutes = $rememberMe ? self::REMEMBER_ME_TTL_MINUTES : self::SESSION_TTL_MINUTES;

        // Generate refresh token using custom claims with TTL
        // Note: We use config to temporarily set TTL for refresh token
        $originalRefreshTtl = config('jwt.refresh_ttl');
        config(['jwt.refresh_ttl' => $refreshTtlMinutes]);

        $refreshToken = JWTAuth::claims([
            'type' => 'refresh',
            'rm' => $rememberMe,
        ])->fromUser($user);

        // Restore original config
        config(['jwt.refresh_ttl' => $originalRefreshTtl]);

        $cookieDomain = config('session.domain');
        $cookieSecure = (bool) (config('session.secure') ?? false);
        $cookieSameSite = config('session.same_site', 'lax');

        // Create HttpOnly cookie for refresh token
        $refreshTokenCookie = $this->cookieJar->make(
            name: 'refresh_token',
            value: $refreshToken,
            minutes: $refreshTtlMinutes,
            path: '/',
            domain: $cookieDomain,
            secure: $cookieSecure,
            httpOnly: true,
            sameSite: $cookieSameSite
        );

        return [
            'token' => $token,
            'user' => $user,
            'expiresIn' => auth()->factory()->getTTL() * 60,
            'rememberMe' => $rememberMe,
            'sessionDuration' => $refreshTtlMinutes * 60,
            'refreshTokenCookie' => $refreshTokenCookie,
        ];
    }
}
