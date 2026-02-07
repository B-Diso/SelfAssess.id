<?php

namespace App\Domain\Auth\Actions;

use App\Domain\User\Models\User;
use App\Exceptions\Domain\InvalidCredentialsException;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieFactory;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Cookie;

class RefreshTokenAction
{
    private const REFRESH_TOKEN_COOKIE = 'refresh_token';
    private const TYPE_CLAIM = 'type';
    private const REMEMBER_ME_CLAIM = 'rm';
    private const TOKEN_TYPE_REFRESH = 'refresh';

    private const TTL_REMEMBER_ME = 43200; // 30 days in minutes
    private const TTL_STANDARD = 480;    // 8 hours in minutes

    public function __construct(
        private readonly CookieFactory $cookieJar
    ) {}

    /**
     * Execute the refresh token action.
     *
     * Validates the refresh token from cookie, rotates it, and returns new access token.
     *
     * @throws InvalidCredentialsException
     */
    public function execute(): array
    {
        // 1. Get refresh token from cookie
        $refreshToken = request()->cookie(self::REFRESH_TOKEN_COOKIE);

        if (empty($refreshToken)) {
            throw new InvalidCredentialsException();
        }

        try {
            // 2. Parse and validate the token
            $payload = JWTAuth::setToken($refreshToken)->getPayload();

            // 3. Validate token type is 'refresh'
            $tokenType = $payload->get(self::TYPE_CLAIM);
            if ($tokenType !== self::TOKEN_TYPE_REFRESH) {
                throw new InvalidCredentialsException();
            }

            // 4. Get user ID and rememberMe flag from payload
            $userId = $payload->get('sub');
            $rememberMe = (bool) $payload->get(self::REMEMBER_ME_CLAIM, false);

            if (empty($userId)) {
                throw new InvalidCredentialsException();
            }

            // 5. Find the user
            $user = User::find($userId);
            if (!$user) {
                throw new InvalidCredentialsException();
            }

            // 6. Invalidate the old refresh token (blacklist it)
            JWTAuth::invalidate(true);

            // 7. Generate new access token for the user
            $accessToken = auth()->login($user);

            // 8. Calculate TTL for new refresh token
            $refreshTtl = $rememberMe ? self::TTL_REMEMBER_ME : self::TTL_STANDARD;

            // 9. Generate new refresh token with rotation
            // Temporarily set config for custom TTL
            $originalRefreshTtl = config('jwt.refresh_ttl');
            config(['jwt.refresh_ttl' => $refreshTtl]);

            $newRefreshToken = JWTAuth::claims([
                'type' => self::TOKEN_TYPE_REFRESH,
                'rm' => $rememberMe,
            ])->fromUser($user);

            // Restore original config
            config(['jwt.refresh_ttl' => $originalRefreshTtl]);

            // 10. Create the new cookie with rotated token
            $cookie = $this->createRefreshTokenCookie($newRefreshToken, $refreshTtl);

            // 11. Queue the cookie to be sent with the response
            $this->cookieJar->queue($cookie);

            return [
                'token' => $accessToken,
                'expiresIn' => auth()->factory()->getTTL() * 60,
            ];
        } catch (TokenInvalidException|JWTException $e) {
            throw new InvalidCredentialsException();
        }
    }

    /**
     * Create a cookie for the refresh token.
     */
    private function createRefreshTokenCookie(string $token, int $ttlMinutes): Cookie
    {
        $cookieDomain = config('session.domain');
        $cookieSecure = (bool) (config('session.secure') ?? false);
        $cookieSameSite = config('session.same_site', 'lax');

        return new Cookie(
            name: self::REFRESH_TOKEN_COOKIE,
            value: $token,
            expire: now()->addMinutes($ttlMinutes),
            path: '/',
            domain: $cookieDomain,
            secure: $cookieSecure,
            httpOnly: true,
            raw: false,
            sameSite: $cookieSameSite
        );
    }
}
