<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Support\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

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
            throw ValidationException::withMessages([
                'password' => ['New password cannot be the same as your current password.'],
            ]);
        }

        $recentPasswords = $user->passwordHistories()
            ->orderBy('created_at', 'desc')
            ->limit(5) // Check last 5 passwords
            ->get();

        foreach ($recentPasswords as $passwordHistory) {
            if (Hash::check($password, $passwordHistory->password_hash)) {
                throw ValidationException::withMessages([
                    'password' => ['Password has been used recently. Please choose a different password.'],
                ]);
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

    /**
     * Create authentication data (session + token)
     */
    public function createAuthData(Request $request, $user, bool $rememberMe, string $authType): array
    {
        $sessionData = null;
        $tokenData = null;

        // Handle web session for web and hybrid auth
        if (in_array($authType, ['web', 'hybrid'])) {
            $sessionData = $this->createWebSession($request, $user, $rememberMe);
        }

        // Handle API token for api and hybrid auth
        if (in_array($authType, ['api', 'hybrid'])) {
            $tokenData = $this->createApiToken($request, $user, $rememberMe);
        }

        return [
            'session' => $sessionData,
            'token' => $tokenData,
            'meta' => $this->buildMetaData($request, $user, $sessionData, $tokenData, $rememberMe, $authType)
        ];
    }

    /**
     * Create web session with cookie
     */
    public function createWebSession(Request $request, $user, bool $rememberMe): array
    {
        // Regenerate session ID for security
        $request->session()->regenerate();
        $sessionId = $request->session()->getId();

        // Set session data
        $request->session()->put('user_id', $user->id);
        $request->session()->put('login_time', now()->timestamp);
        $request->session()->put('ip_address', $request->ip());

        // Handle remember me cookie
        if ($rememberMe) {
            $rememberToken = Hash::make($user->email . time());
            $user->setRememberToken($rememberToken);
            $user->save();

            Cookie::queue(
                'remember_web_token',
                $rememberToken,
                config('auth.remember_duration', 43200), // 30 days in minutes
                config('session.path'),
                config('session.domain'),
                config('session.secure'),
                config('session.http_only')
            );
        }

        return [
            'session_id' => $sessionId,
            'expires_at' => $rememberMe
                ? now()->addDays(30)->toISOString()
                : now()->addMinutes(config('session.lifetime', 120))->toISOString()
        ];
    }

    /**
     * Create Sanctum API token
     */
    private function createApiToken(Request $request, $user, bool $rememberMe): array
    {
        $tokenName = $rememberMe ? 'remember-api-token' : 'session-api-token';
        $tokenExpiration = $rememberMe ? now()->addDays(30) : now()->addHours(8);

        // Remove existing tokens of the same type
        $user->tokens()->where('name', $tokenName)->delete();

        // Create new token with expiration
        $token = $user->createToken($tokenName, ['*'], $tokenExpiration);

        // Add metadata to token
        if (method_exists($token->accessToken, 'update')) {
            $token->accessToken->update([
                'metadata' => json_encode([
                    'remember_me' => $rememberMe,
                    'login_method' => 'hybrid',
                    'created_via' => 'login',
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'created_at' => now()->toISOString()
                ])
            ]);
        }

        return [
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $tokenExpiration->toISOString(),
            'remember_me' => $rememberMe
        ];
    }

    /**
     * Build meta data for response
     */
    private function buildMetaData(Request $request, $user, ?array $sessionData, ?array $tokenData, bool $rememberMe, string $authType): array
    {
        $meta = [
            'login_at' => now()->toISOString(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'auth_type' => $authType,
            'remember_me' => $rememberMe
        ];

        if ($sessionData) {
            $meta['session_id'] = $sessionData['session_id'];
            $meta['session_expires_at'] = $sessionData['expires_at'];
        }

        if ($tokenData) {
            $meta['api_token'] = $tokenData['token'];
            $meta['token_type'] = $tokenData['token_type'];
            $meta['token_expires_at'] = $tokenData['expires_at'];
        }

        return $meta;
    }

    /**
     * Handle failed login attempt
     */
    public function handleFailedLogin(Request $request, string $email): void
    {
        $rateLimitKey = 'login_attempts:' . $request->ip();
        $attempts = cache()->get($rateLimitKey, 0);

        cache()->put($rateLimitKey, $attempts + 1, now()->addMinutes(15));

        Log::warning('Failed login attempt', [
            'email' => $email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attempts' => $attempts + 1,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Update user login information
     */
    public function updateUserLoginInfo($user, Request $request): void
    {
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_login_user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Log successful login
     */
    public function logSuccessfulLogin($user, Request $request, array $authData): void
    {
        Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'remember_me' => $authData['meta']['remember_me'] ?? false,
            'auth_type' => $authData['meta']['auth_type'] ?? 'hybrid',
            'session_id' => $authData['session']['session_id'] ?? null,
            'token_expires_at' => $authData['token']['expires_at'] ?? null,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Clear rate limiting on successful login
     */
    public function clearRateLimit(Request $request): void
    {
        $rateLimitKey = 'login_attempts:' . $request->ip();
        cache()->forget($rateLimitKey);
    }
}
