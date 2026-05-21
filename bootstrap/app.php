<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid',
                    'error_code' => 'VALIDATION_FAILED',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'error_code' => 'UNAUTHENTICATED',
                ], 401);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $message = 'Resource not found';
                $path = $request->path();

                if (str_contains($path, 'api/v1/students')) {
                    $message = 'Student not found';
                } elseif (str_contains($path, 'api/v1/teachers')) {
                    $message = 'Teacher not found';
                } elseif (str_contains($path, 'api/v1/classes')) {
                    $message = 'Class not found';
                } elseif (str_contains($path, 'api/v1/activities')) {
                    $message = 'Activity not found';
                } elseif (str_contains($path, 'api/v1/scores')) {
                    $message = 'Score not found';
                } elseif (str_contains($path, 'api/v1/materials')) {
                    $message = 'Material not found';
                } elseif (str_contains($path, 'api/v1/users')) {
                    $message = 'User not found';
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error_code' => 'RESOURCE_NOT_FOUND',
                ], 404);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $headers = $e->getHeaders();
                $retryAfter = $headers['Retry-After'] ?? 60;

                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please slow down.',
                    'error_code' => 'RATE_LIMIT_EXCEEDED',
                    'retry_after' => (int) $retryAfter,
                ], 429);
            }
        });
    })->create();
