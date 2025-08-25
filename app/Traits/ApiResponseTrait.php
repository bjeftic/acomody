<?php

namespace App\Traits;

use App\Data\Response\ApiResponse;
use App\Data\Error\ValidationErrorData;
use App\Data\Meta\PaginationMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

trait ApiResponseTrait
{
    /**
     * Return a successful API response
     */
    protected function successResponse(
        string $message = 'Operation completed successfully',
        mixed $data = null,
        ?array $meta = null,
        int $statusCode = 200
    ): JsonResponse {
        return ApiResponse::success($message, $data, $meta)
            ->toResponse(request())
            ->setStatusCode($statusCode);
    }

    /**
     * Return an error API response
     */
    protected function errorResponse(
        string $message = 'Operation failed',
        ?array $errors = null,
        mixed $data = null,
        ?array $meta = null,
        int $statusCode = 400
    ): JsonResponse {
        return ApiResponse::error($message, $errors, $data, $meta)
            ->toResponse(request())
            ->setStatusCode($statusCode);
    }

    /**
     * Return a validation error response
     */
    protected function validationErrorResponse(
        ValidationException $exception,
        string $message = 'Validation failed'
    ): JsonResponse {
        $validationErrors = ValidationErrorData::fromArray($exception->errors());

        return $this->errorResponse(
            message: $message,
            errors: $validationErrors,
            statusCode: 422
        );
    }

    /**
     * Return a paginated response
     */
    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        return $this->successResponse(
            message: $message,
            data: $paginator->items(),
            meta: [
                'pagination' => PaginationMeta::fromPaginator($paginator)
            ]
        );
    }

    /**
     * Return a created resource response
     */
    protected function createdResponse(
        string $message = 'Resource created successfully',
        mixed $data = null,
        ?array $meta = null
    ): JsonResponse {
        return $this->successResponse($message, $data, $meta, 201);
    }

    /**
     * Return a not found response
     */
    protected function notFoundResponse(
        string $message = 'Resource not found'
    ): JsonResponse {
        return $this->errorResponse($message, statusCode: 404);
    }

    /**
     * Return an unauthorized response
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthorized access'
    ): JsonResponse {
        return $this->errorResponse($message, statusCode: 401);
    }

    /**
     * Return a forbidden response
     */
    protected function forbiddenResponse(
        string $message = 'Access forbidden'
    ): JsonResponse {
        return $this->errorResponse($message, statusCode: 403);
    }

    /**
     * Return a conflict response
     */
    protected function conflictResponse(
        string $message = 'Resource conflict',
        ?array $errors = null
    ): JsonResponse {
        return $this->errorResponse($message, $errors, statusCode: 409);
    }

    /**
     * Return an internal server error response
     */
    protected function serverErrorResponse(
        string $message = 'Internal server error',
        ?array $meta = null
    ): JsonResponse {
        return $this->errorResponse(
            message: $message,
            meta: array_merge([
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'timestamp' => now()->toISOString(),
                'request_id' => request()->header('X-Request-ID', uniqid())
            ], $meta ?? []),
            statusCode: 500
        );
    }

    /**
     * Return a rate limit exceeded response
     */
    protected function rateLimitResponse(
        string $message = 'Rate limit exceeded',
        ?int $retryAfter = null
    ): JsonResponse {
        $response = $this->errorResponse($message, statusCode: 429);

        if ($retryAfter) {
            $response->header('Retry-After', $retryAfter);
        }

        return $response;
    }

    /**
     * Return a maintenance mode response
     */
    protected function maintenanceResponse(
        string $message = 'Service temporarily unavailable'
    ): JsonResponse {
        return $this->errorResponse(
            message: $message,
            meta: [
                'error_code' => 'MAINTENANCE_MODE',
                'timestamp' => now()->toISOString()
            ],
            statusCode: 503
        );
    }
}
