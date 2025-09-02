<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Support\ApiResponse;

class AuthService
{
    /**
     * Apply rate limiting to login attempts
     */
    public function throttleLoginAttempts($rateLimitKey): ?JsonResponse
    {
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return ApiResponse::rateLimitExceeded('Too many login attempts. Please try again in ' . $seconds . ' seconds.');
        }

        RateLimiter::hit($rateLimitKey, 300); // 5 minutes decay

        return null;
    }

    /**
     * Apply rate limiting to password reset attempts
     */
    public function throttlePasswordResetAttempts(Request $request): ?JsonResponse
    {
        $key = 'password-reset:' . $request->ip() . ':' . $request->input('email');

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return ApiResponse::rateLimitExceeded('Too many password reset attempts. Please try again in ' . $seconds . ' seconds.');
        }

        RateLimiter::hit($key, 300); // 5 minutes decay

        return null;
    }

    /**
     * Check if password was used recently
     */
    public function checkPasswordHistory(User $user, string $password): ?JsonResponse
    {
        // Check current password
        if (Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'New password cannot be the same as your current password.',
            ], 401);
        }

        $recentPasswords = $user->passwordHistories()
            ->orderBy('created_at', 'desc')
            ->limit(5) // Check last 5 passwords
            ->get();

        foreach ($recentPasswords as $passwordHistory) {
            if (Hash::check($password, $passwordHistory->password_hash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password has been used recently. Please choose a different password.',
                ], 401);
            }
        }

        return null;
    }

    /**
     * Revoke all user tokens for security
     */
    public function revokeUserTokens(User $user): void
    {
        // Revoke all Sanctum tokens
        $user->tokens()->delete();
    }

    /**
     * Store password in history for future checks
     */
    public function storePasswordHistory(User $user, string $hashedPassword): void
    {
        $user->passwordHistories()->create([
            'password_hash' => $hashedPassword,
            'created_at' => now(),
        ]);
    }
}
