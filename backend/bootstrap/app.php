<?php

use App\Providers\RouteServiceProvider;
use App\Helpers\CamelCaseHelper;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\RefreshToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Laravel 12 built-in CORS middleware (configured via config/cors.php)
        $middleware->use([\Illuminate\Http\Middleware\HandleCors::class]);

        $middleware->alias([
            'jwt.auth' => Authenticate::class,
            'jwt.refresh' => RefreshToken::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\App\Exceptions\Domain\DomainException $e) {
            $errorCode = $e->getErrorCode();

            // Convert error code to camelCase if it contains underscores
            $camelCaseErrorCode = CamelCaseHelper::toCamelCase($errorCode);

            // Convert details to camelCase if it's an array
            $details = $e->getDetails();
            if (is_array($details)) {
                $details = CamelCaseHelper::convertToCamelCase($details);
            }

            return response()->json([
                'error' => [
                    'errorCode' => $camelCaseErrorCode,
                    'message' => $e->getMessage(),
                    'details' => $details,
                ],
            ], $e->getCode() ?: 400);
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'error' => [
                    'errorCode' => 'authorizationError',
                    'message' => 'Unauthorized access',
                    'details' => null,
                ],
            ], 403);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json([
                'error' => [
                    'errorCode' => 'authorizationError',
                    'message' => 'Unauthorized access',
                    'details' => null,
                ],
            ], 403);
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => [
                    'errorCode' => 'notFound',
                    'message' => 'Resource not found',
                    'details' => null,
                ],
            ], 404);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            if ($e->getPrevious() instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'error' => [
                        'errorCode' => 'notFound',
                        'message' => 'Resource not found',
                        'details' => null,
                    ],
                ], 404);
            }
            return response()->json([
                'error' => [
                    'errorCode' => 'notFound',
                    'message' => 'Route not found',
                    'details' => null,
                ],
            ], 404);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e) {
            return response()->json([
                'error' => [
                    'errorCode' => 'methodNotAllowed',
                    'message' => 'Method not allowed',
                    'details' => null,
                ],
            ], 405);
        });
    })->create();
