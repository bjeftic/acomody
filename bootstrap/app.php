<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Exceptions\ApiExceptionHandler;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web();
        $middleware->api();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, Request $request) {
            // Only for API, laravel will handle web requests normally
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }

            $apiHandler = new ApiExceptionHandler();

            // Try to find a specific handler for the exception
            foreach (ApiExceptionHandler::$handlers as $exceptionClass => $method) {
                if ($e instanceof $exceptionClass) {
                    return $apiHandler->$method($e, $request);
                }
            }

            // Fallback - generic handler if exists
            if (method_exists($apiHandler, 'handleGenericException')) {
                return $apiHandler->handleGenericException($e, $request);
            }

            return response()->json([
                'error' => [
                    'type' => basename(str_replace('\\', '/', get_class($e))),
                    'status' => 500,
                    'message' => $e->getMessage() ?: 'An unexpected error occurred.',
                    'timestamp' => now()->toISOString(),
                    'debug' => app()->environment('local', 'testing') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => explode("\n", $e->getTraceAsString())
                    ] : null
                ]
            ], 500);
        });
    })
    ->create();
