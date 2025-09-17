<?php

namespace App\Http\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Contracts\Support\Responsable;

class ApiResponse implements Responsable
{
    protected string $message;
    protected ?JsonResource $data;
    protected ?array $errors;
    protected ?array $meta;
    protected bool $success;

    public function __construct(
        string $message,
        ?JsonResource $data = null,
        ?array $errors = null,
        ?array $meta = null,
        bool $success = true
    ) {
        $this->message = $message;
        $this->data = $data;
        $this->errors = $errors;
        $this->meta = $meta;
        $this->success = $success;
    }

    /**
     * Create a successful API response
     */
    public static function success(
        string $message = 'Operation completed successfully',
        ?JsonResource $data = null,
        ?array $meta = null,
        ?int $statusCode = 200
    ): JsonResponse {
        return (new self($message, $data, null, $meta, true))
            ->toResponse(request())
            ->setStatusCode($statusCode);
    }

    /**
     * Create an error API response
     */
    public static function error(
        string $message = 'Operation failed',
        ?JsonResource $data = null,
        ?array $meta = null,
        ?int $statusCode = 400
    ): JsonResponse {
        return (new self($message, $data, null, $meta, false))
            ->toResponse(request())
            ->setStatusCode($statusCode);
    }


    /**
     * Convert the response to a JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        $responseData = [
            'success' => $this->success,
            'message' => $this->message,
        ];

        if ($this->data !== null) {
            $responseData['data'] = $this->data->resolve($request);
        }

        if ($this->errors !== null) {
            $responseData['errors'] = $this->errors;
        }

        if ($this->meta !== null) {
            $responseData['meta'] = $this->meta;
        }

        return new JsonResponse($responseData);
    }

    /**
     * Get the message
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the data
     */
    public function getData(): ?JsonResource
    {
        return $this->data;
    }

    /**
     * Get the errors
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Get the meta information
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }

    /**
     * Check if the response is successful
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Set additional meta information
     */
    public function withMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta ?? [], $meta);
        return $this;
    }

    /**
     * Set errors
     */
    public function withErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public static function created(string $message, ?JsonResource $data = null): JsonResponse
    {
        return self::success($message, $data)
            ->setStatusCode(201);
    }

    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message)
            ->setStatusCode(404);
    }

    public static function unauthorized(string $message = 'Unauthorized access'): JsonResponse
    {
        return self::error($message)
            ->setStatusCode(401);
    }

    public static function forbidden(string $message = 'Forbidden access'): JsonResponse
    {
        return self::error($message)
            ->setStatusCode(403);
    }

    public static function rateLimitExceeded(string $message = 'Too many requests'): JsonResponse
    {
        return self::error($message)
            ->setStatusCode(429);
    }
}
