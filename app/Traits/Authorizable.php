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
        // Before retrieving (for single model retrieval)
        static::retrieved(function ($model) {
            $user = Auth::user();

            // Special case: User model when not authenticated
            if ($model instanceof User && !Auth::check()) {
                return;
            }

            // Superadmin bypasses all checks
            if ($user && $user->is_superadmin) {
                return;
            }

            // Check authorization
            if (!$model->canBeReadBy($user)) {
                throw new AccessDeniedHttpException(
                    'You are not authorized to view this ' . class_basename(static::class) . '.'
                );
            }
        });

        // Before creating
        static::creating(function ($model) {
            $user = Auth::user();

            // Superadmin bypasses all checks
            if ($user && $user->is_superadmin) {
                return;
            }

            if (!$model->canBeCreatedBy($user)) {
                throw new AccessDeniedHttpException(
                    'You are not authorized to create ' . class_basename(static::class) . '.'
                );
            }
        });

        // Before updating
        static::updating(function ($model) {
            $user = Auth::user();

            // Special case: User model - allow users to update their own record
            if ($model instanceof User && $user && $user->id === $model->id) {
                return;
            }

            // Superadmin bypasses all checks
            if ($user && $user->is_superadmin) {
                return;
            }

            if (!$model->canBeUpdatedBy($user)) {
                throw new AccessDeniedHttpException(
                    'You are not authorized to update this ' . class_basename(static::class) . '.'
                );
            }
        });

        // Before deleting
        static::deleting(function ($model) {
            $user = Auth::user();

            // Superadmin bypasses all checks
            if ($user && $user->is_superadmin) {
                return;
            }

            if (!$model->canBeDeletedBy($user)) {
                throw new AccessDeniedHttpException(
                    'You are not authorized to delete this ' . class_basename(static::class) . '.'
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
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User|null $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAuthorized($query, $user = null)
    {
        $user = $user ?? Auth::user();

        // If user is superadmin, return all records
        if ($user && $user->is_superadmin) {
            return $query;
        }

        // For complex filtering, implement custom scopeAuthorized in your model
        // This is just a basic implementation that returns the query
        // Individual models should override this for specific authorization logic

        return $query;
    }
}
