<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Exceptions\ApiExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'super.admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'redirect.super.admin' => \App\Http\Middleware\RedirectSuperAdminMiddleware::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\DetectCurrency::class,
        ]);

        // Stateful API for Sanctum - handles EncryptCookies, StartSession, CSRF, AuthenticateSession
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, Request $request) {
            // Only for API requests
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }

            $apiHandler = new ApiExceptionHandler();

            // Handle specific exception types with proper order
            // Order matters - check most specific first!

            // 401 - Not authenticated (not logged in)
            if ($e instanceof AuthenticationException) {
                return $apiHandler->handleAuthenticationException($e, $request);
            }

            // 403 - Not authorized (logged in but no permission)
            if ($e instanceof AuthorizationException) {
                return $apiHandler->handleAuthorizationException($e, $request);
            }

            // 403 - Access denied (Symfony exception)
            if ($e instanceof AccessDeniedHttpException) {
                return $apiHandler->handleAccessDeniedException($e, $request);
            }

            // Try to find other specific handlers
            foreach (ApiExceptionHandler::$handlers as $exceptionClass => $method) {
                // Skip already handled exceptions
                if ($exceptionClass === AuthenticationException::class ||
                    $exceptionClass === AuthorizationException::class ||
                    $exceptionClass === AccessDeniedHttpException::class) {
                    continue;
                }

                if ($e instanceof $exceptionClass) {
                    return $apiHandler->$method($e, $request);
                }
            }

            // Fallback to generic handler
            return $apiHandler->handleGenericException($e, $request);
        });
    })
    ->create();
