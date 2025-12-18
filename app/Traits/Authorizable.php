<?php

namespace App\Traits;

use App\Exceptions\AuthorizationMethodMissingException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

trait Authorizable
{
    /**
     * Boot the authorizable trait for a model.
     */
    public static function bootAuthorizable(): void
    {
        // Validate that all required methods exist
        static::validateAuthorizableMethods();

        // Automatically check authorization on model events
        static::registerAuthorizationEvents();
    }

    /**
     * Register automatic authorization checks on model events
     */
    protected static function registerAuthorizationEvents(): void
    {
        $user = auth()->user();

        if (is_null($user) || !$user->is_superadmin) {
            return;
        }

        // Before creating
        static::creating(function ($model) use ($user) {
            if (!$model->canBeCreatedBy($user)) {
                throw new AccessDeniedHttpException(
                    'You are not authorized to create ' . class_basename(static::class) . '.'
                );
            }
        });

        // Before updating
        static::updating(function ($model) use ($user) {
            $user = Auth::user();

            if ($model instanceof User) {
                // If the model is a User, allow users to update their own record
                if ($user && $user->id === $model->id) {
                    return;
                }
            }

            if (Auth::user()->is_superadmin) {
                return;
            }

            if (!$model->canBeUpdatedBy($user)) {
                throw new AccessDeniedHttpException(
                    'You are not authorized to update this ' . class_basename(static::class) . '.'
                );
            }
        });

        // Before deleting
        static::deleting(function ($model) use ($user) {
            if (Auth::user()->is_superadmin) {
                return;
            }
            if (!$model->canBeDeletedBy($user)) {
                throw new AccessDeniedHttpException(
                    'You are not authorized to delete this ' . class_basename(static::class) . '.'
                );
            }
        });

        // Before retrieving (for single model retrieval)
        static::retrieved(function ($model) use ($user) {

            if ($model instanceof User && !Auth::check()) {
                return;
            }

            if (Auth::user()->is_superadmin) {
                return;
            }

            // Check if this was a single model retrieval (not from query)
            // Only check if explicitly loaded via find(), first(), etc.
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);

            // Check if called from route model binding or direct retrieval
            $isDirectRetrieval = collect($backtrace)->contains(function ($trace) {
                return isset($trace['class']) &&
                    (str_contains($trace['class'], 'Router') ||
                        str_contains($trace['class'], 'Controller') ||
                        isset($trace['function']) && in_array($trace['function'], ['find', 'findOrFail', 'first', 'firstOrFail']));
            });

            if ($isDirectRetrieval && !$model->canBeReadBy($user)) {
                throw new AccessDeniedHttpException(
                    'You are not authorized to view this ' . class_basename(static::class) . '.'
                );
            }
        });
    }

    /**
     * Validate that all required authorization methods are implemented
     *
     * @throws AuthorizationMethodMissingException
     */
    protected static function validateAuthorizableMethods(): void
    {
        $requiredMethods = [
            'canBeReadBy',
            'canBeCreatedBy',
            'canBeUpdatedBy',
            'canBeDeletedBy'
        ];

        $missingMethods = [];

        foreach ($requiredMethods as $method) {
            if (!method_exists(static::class, $method)) {
                $missingMethods[] = $method;
            }
        }

        if (!empty($missingMethods)) {
            throw new AuthorizationMethodMissingException(
                static::class,
                $missingMethods
            );
        }
    }

    /**
     * Check if the user can read this resource
     *
     * @param \App\Models\User|null $user
     * @return bool
     */
    abstract public function canBeReadBy($user): bool;

    /**
     * Check if the user can create this resource
     *
     * @param \App\Models\User|null $user
     * @return bool
     */
    abstract public function canBeCreatedBy($user): bool;

    /**
     * Check if the user can update this resource
     *
     * @param \App\Models\User|null $user
     * @return bool
     */
    abstract public function canBeUpdatedBy($user): bool;

    /**
     * Check if the user can delete this resource
     *
     * @param \App\Models\User|null $user
     * @return bool
     */
    abstract public function canBeDeletedBy($user): bool;

    /**
     * Scope to automatically filter models by read authorization
     */
    public function scopeAuthorized($query, $user = null)
    {
        $user = $user ?? auth()->user();

        // If it's a simple query, we can't easily filter by canBeReadBy
        // So we'll just return the query and rely on retrieved event
        // For complex filtering, implement custom scopeAuthorized in your model

        return $query;
    }
}
