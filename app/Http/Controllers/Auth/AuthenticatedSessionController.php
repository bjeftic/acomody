<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserLogInRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Support\ApiResponse;
use App\Services\AuthService;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthenticatedSessionController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * SPA Login - Session-based authentication
     */
    public function store(UserLogInRequest $request): JsonResponse
    {
        try {
            // Rate limiting
            $rateLimitKey = 'login_attempts:' . $request->ip();
            $rateLimitResponse = $this->authService->throttleLoginAttempts($rateLimitKey);
            if ($rateLimitResponse) {
                return $rateLimitResponse;
            }

            $validatedData = $request->validated();
            $credentials = [
                'email' => $validatedData['email'],
                'password' => $validatedData['password']
            ];
            $rememberMe = $validatedData['remember_me'] ?? false;

            // Attempt login
            if (!Auth::guard('web')->attempt($credentials, $rememberMe)) {
                $this->authService->handleFailedLogin($request, $validatedData['email']);
                throw ValidationException::withMessages([
                    'email' => ['Invalid email or password.']
                ]);
            }

            $user = Auth::guard('web')->user();

            // Regenerate session only if session exists
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $this->authService->updateUserLoginInfo($user, $request);
            $this->authService->clearRateLimit($request);

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return ApiResponse::success(
                'User logged in successfully.',
                new UserResource($user),
                [
                    'login_at' => now()->toISOString(),
                    'ip_address' => $request->ip(),
                ]
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Login failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $request->input('email', 'unknown'),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::error('Login failed. Please try again later.', null, null, 500);
        }
    }

    /**
     * Logout
     */
    public function destroyApi(Request $request): JsonResponse
    {
        try {
            $user = Auth::guard('web')->user();

            if (!$user) {
                return ApiResponse::error('Unauthorized', null, null, 401);
            }

            // Update logout info
            $user->update([
                'last_logout_at' => now(),
                'last_logout_ip' => $request->ip(),
            ]);

            // Logout and invalidate session
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            Log::info('User logged out', ['user_id' => $user->id]);

            return ApiResponse::success('User logged out successfully.');
        } catch (Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::error('Logout failed', null, null, 500);
        }
    }
}
