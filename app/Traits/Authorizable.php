<?php

namespace App\Traits;

use App\Exceptions\AuthorizationMethodMissingException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

trait Authorizable
{
    /**
     * Static flag to bypass authorization checks (e.g. for console commands)
     */
    protected static bool $authorizationDisabled = false;

    /**
     * Boot the authorizable trait for a model.
     */
    public static function bootAuthorizable(): void
    {
        static::validateAuthorizableMethods();
        static::registerAuthorizationEvents();
    }

    /**
     * Execute a callback without authorization checks
     */
    public static function withoutAuthorization(callable $callback): mixed
    {
        static::$authorizationDisabled = true;

        try {
            return $callback();
        } finally {
            static::$authorizationDisabled = false;
        }
    }

    /**
     * Register automatic authorization checks on model events
     */
    protected static function registerAuthorizationEvents(): void
    {
        static::retrieved(function ($model) {
            if (static::$authorizationDisabled) {
                return;
            }

            $user = Auth::user();

            // Special case: User model when not authenticated
            if ($model instanceof User && !Auth::check()) {
                return;
            }

            // Superadmin bypasses all checks
            if ($user && $user->is_superadmin) {
                return;
            }

            if (!$model->canBeReadBy($user)) {
                throw new AccessDeniedHttpException(
                    'You are not authorized to view this ' . class_basename(static::class) . '.'
                );
            }
        });

        static::creating(function ($model) {
            if (static::$authorizationDisabled) {
                return;
            }

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

        static::updating(function ($model) {
            if (static::$authorizationDisabled) {
                return;
            }

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

        static::deleting(function ($model) {
            if (static::$authorizationDisabled) {
                return;
            }

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
            'canBeDeletedBy',
        ];

        $missingMethods = array_filter(
            $requiredMethods,
            fn($method) => !method_exists(static::class, $method)
        );

        if (!empty($missingMethods)) {
            throw new AuthorizationMethodMissingException(
                static::class,
                array_values($missingMethods)
            );
        }
    }

    /**
     * Check if the user can read this resource
     */
    abstract public function canBeReadBy($user): bool;

    /**
     * Check if the user can create this resource
     */
    abstract public function canBeCreatedBy($user): bool;

    /**
     * Check if the user can update this resource
     */
    abstract public function canBeUpdatedBy($user): bool;

    /**
     * Check if the user can delete this resource
     */
    abstract public function canBeDeletedBy($user): bool;

    /**
     * Scope to automatically filter models by read authorization
     */
    public function scopeAuthorized($query, $user = null)
    {
        $user = $user ?? Auth::user();

        if ($user && $user->is_superadmin) {
            return $query;
        }

        return $query;
    }
}
