<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Throwable;
use Illuminate\Support\Facades\Auth;

class ApiExceptionHandler
{
    /**
     * Map of exception classes to their handler methods
     */
    public static array $handlers = [
        AuthenticationException::class => 'handleAuthenticationException',
        AccessDeniedHttpException::class => 'handleAuthenticationException',
        AuthorizationException::class => 'handleAuthorizationException',
        ValidationException::class => 'handleValidationException',
        ModelNotFoundException::class => 'handleModelNotFoundException',
        NotFoundHttpException::class => 'handleNotFoundHttpException',
        MethodNotAllowedHttpException::class => 'handleMethodNotAllowedException',
        HttpException::class => 'handleHttpException',
        QueryException::class => 'handleQueryException',
        InvalidSignatureException::class => 'handleInvalidSignatureException',
        ThrottleRequestsException::class => 'handleThrottleException',
        PostTooLargeException::class => 'handlePostTooLargeException',
        ServiceUnavailableHttpException::class => 'handleServiceUnavailableException',
        BadRequestException::class => 'handleBadRequestException',
        MissingAbilityException::class => 'handleMissingAbilityException',
    ];

    /**
     * Handle authentication exceptions
     */
    public function handleAuthenticationException(
        AuthenticationException|AccessDeniedHttpException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'Authentication failed', 401);

        return response()->json([
            'error' => [
                'type' => $this->getExceptionType($e),
                'status' => 401,
                'message' => 'Authentication required. Please provide valid credentials.',
                'timestamp' => now()->toISOString(),
            ]
        ], 401);
    }

    /**
     * Handle authorization exceptions
     */
    public function handleAuthorizationException(
        AuthorizationException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'Authorization failed', 403);

        return response()->json([
            'error' => [
                'type' => $this->getExceptionType($e),
                'status' => 403,
                'message' => 'You do not have permission to perform this action.',
                'timestamp' => now()->toISOString(),
            ]
        ], 403);
    }

    /**
     * Handle validation exceptions
     */
    public function handleValidationException(
        ValidationException $e,
        Request $request
    ): JsonResponse {
        $errors = [];

        foreach ($e->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $errors[] = [
                    'field' => $field,
                    'message' => $message,
                ];
            }
        }

        $this->logException($e, 'Validation failed', 422, ['errors' => $errors]);

        return response()->json([
                'type' => $this->getExceptionType($e),
                'status' => 422,
                'message' => 'The provided data is invalid.',
                'timestamp' => now()->toISOString(),
                'validation_errors' => $errors,
        ], 422);
    }

    /**
     * Handle model not found exceptions (database records)
     */
    public function handleModelNotFoundException(
        ModelNotFoundException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'Resource not found', 404);

        return response()->json([
            'error' => [
                'type' => $this->getExceptionType($e),
                'status' => 404,
                'message' => 'The requested resource was not found.',
                'timestamp' => now()->toISOString(),
            ]
        ], 404);
    }

    public function handleNotFoundHttpException(
        NotFoundHttpException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'Endpoint not found', 404);

        return response()->json([
            'error' => [
                'type' => $this->getExceptionType($e),
                'status' => 404,
                'message' => 'The requested resource or endpoint was not found.',
                'timestamp' => now()->toISOString(),
            ]
        ], 404);
    }

    /**
     * Handle method not allowed exceptions
     */
    public function handleMethodNotAllowedException(
        MethodNotAllowedHttpException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'Method not allowed', 405);

        return response()->json([
            'error' => [
                'type' => $this->getExceptionType($e),
                'status' => 405,
                'message' => "The {$request->method()} method is not allowed for this endpoint.",
                'timestamp' => now()->toISOString(),
                'allowed_methods' => $e->getHeaders()['Allow'] ?? 'Unknown',
            ]
        ], 405);
    }

    /**
     * Handle general HTTP exceptions
     */
    public function handleHttpException(HttpException $e, Request $request): JsonResponse
    {
        $this->logException($e, 'HTTP exception occurred', $e->getStatusCode());

        return response()->json([
            'error' => [
                'type' => $this->getExceptionType($e),
                'status' => $e->getStatusCode(),
                'message' => $e->getMessage() ?: 'An HTTP error occurred.',
                'timestamp' => now()->toISOString(),
            ]
        ], $e->getStatusCode());
    }

    /**
     * Handle database query exceptions
     */
    public function handleQueryException(QueryException $e, Request $request): JsonResponse
    {
        $this->logException($e, 'Database query failed', 500, ['sql' => $e->getSql()]);

        // Handle specific database constraint violations
        $errorCode = $e->errorInfo[1] ?? null;

        switch ($errorCode) {
            case 1451: // Foreign key constraint violation
                return response()->json([
                    'error' => [
                        'type' => $this->getExceptionType($e),
                        'status' => 409,
                        'message' => 'Cannot delete this resource because it is referenced by other records.',
                        'timestamp' => now()->toISOString(),
                    ]
                ], 409);

            case 1062: // Duplicate entry
                return response()->json([
                    'error' => [
                        'type' => $this->getExceptionType($e),
                        'status' => 409,
                        'message' => 'A record with this information already exists.',
                        'timestamp' => now()->toISOString(),
                    ]
                ], 409);

            default:
                return response()->json([
                    'error' => [
                        'type' => $this->getExceptionType($e),
                        'status' => 500,
                        'message' => 'A database error occurred. Please try again later.',
                        'timestamp' => now()->toISOString(),
                    ]
                ], 500);
        }
    }

    /**
     * Invalid signature exception handler
     */
    public function handleInvalidSignatureException(
        InvalidSignatureException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'Invalid or expired URL signature', 403);
        return response()->json([
            'error' => [
                'type' => $this->getExceptionType($e),
                'status' => 403,
                'message' => 'The URL signature is invalid or has expired.',
                'timestamp' => now()->toISOString(),
            ]
        ], 403);
    }

    /**
     * Throttle exception handler
     */
    public function handleThrottleException(
        ThrottleRequestsException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'Too many requests', 429);

        $retryAfter = $e->getHeaders()['Retry-After'] ?? null;

        return response()->json([
                'type' => $this->getExceptionType($e),
                'status' => 429,
                'message' => 'Too many requests. Please try again later.',
                'timestamp' => now()->toISOString(),
                'retry_after' => $retryAfter ? (int)$retryAfter : null,
        ], 429)->withHeaders($e->getHeaders());
    }

    /**
     * Handle post too large exception (file upload size exceeded)
     */
    public function handlePostTooLargeException(
        PostTooLargeException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'Request payload too large', 413);

        return response()->json([
            'error' => [
                'type' => $this->getExceptionType($e),
                'status' => 413,
                'message' => 'The uploaded file or request payload is too large.',
                'timestamp' => now()->toISOString(),
                'max_size' => ini_get('upload_max_filesize'),
            ]
        ], 413);
    }

    /**
     * Handle service unavailable exception (maintenance mode)
     */
    public function handleServiceUnavailableException(
        ServiceUnavailableHttpException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'Service unavailable', 503);

        return response()->json([
            'error' => [
                'type' => $this->getExceptionType($e),
                'status' => 503,
                'message' => 'Service temporarily unavailable. Please try again later.',
                'timestamp' => now()->toISOString(),
            ]
        ], 503);
    }

    /**
     * Handle bad request exception (malformed JSON, etc.)
     */
    public function handleBadRequestException(
        BadRequestException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'Bad request', 400);

        return response()->json([
            'error' => [
                'type' => $this->getExceptionType($e),
                'status' => 400,
                'message' => 'The request could not be understood. Please check your request format.',
                'timestamp' => now()->toISOString(),
            ]
        ], 400);
    }

    /**
     * Handle missing ability exception (Sanctum token permissions)
     */
    public function handleMissingAbilityException(
        MissingAbilityException $e,
        Request $request
    ): JsonResponse {
        $this->logException($e, 'Missing token ability', 403);

        return response()->json([
            'error' => [
                'type' => $this->getExceptionType($e),
                'status' => 403,
                'message' => 'Your access token does not have the required permissions for this action.',
                'timestamp' => now()->toISOString(),
            ]
        ], 403);
    }

    /**
     * Handle all other exceptions (fallback handler)
     */
    public function handleGenericException(
        Throwable $e,
        Request $request
    ): JsonResponse {
        // Log with high severity for unexpected exceptions
        Log::error('Unhandled exception occurred', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
        ]);

        // Do not expose sensitive details in production
        $message = config('app.debug')
            ? $e->getMessage()
            : 'An unexpected error occurred. Please try again later.';

        return response()->json([
            'error' => [
                'type' => $this->getExceptionType($e),
                'status' => 500,
                'message' => $message,
                'timestamp' => now()->toISOString(),
            ]
        ], 500);
    }

    /**
     * Extract a clean exception type name
     */
    private function getExceptionType(Throwable $e): string
    {
        $className = basename(str_replace('\\', '/', get_class($e)));
        return $className;
    }

    /**
     * Log exception with context
     */
    private function logException(Throwable $e, string $message, string $statusCode, array $context = []): void
    {
        $logContext = array_merge([
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_id' => Auth::id(),
        ], $context);

        if ($statusCode >= 500) {
            Log::error($message, $logContext);
        } else {
            Log::warning($message, $logContext);
        }
    }
}
