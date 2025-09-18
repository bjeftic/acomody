<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserLogInRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Http\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Services\AuthService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Authenticate user login
     *
     * @OA\Post(
     *     path="/log-in",
     *     operationId="loginUser",
     *     tags={"Authentication"},
     *     summary="User log in",
     *     description="Authenticates user with email and password",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User login credentials",
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.smith@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="remember_me", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User successfully logged in",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User logged in successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(ref="#/components/schemas/User")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="login_at", type="string", format="date-time", example="2025-08-31T21:47:00.911232Z"),
     *                 @OA\Property(property="ip_address", type="string", example="172.19.0.1"),
     *                 @OA\Property(property="user_agent", type="string", example="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36")
     *             )
     *         )
     *     )
     * )
     */
    public function store(UserLogInRequest $request): JsonResponse
    {
        try {
            // Rate limiting check
            $rateLimitKey = 'login_attempts:' . $request->ip();
            $this->authService->throttleLoginAttempts($rateLimitKey);

            // Extract validated data
            $validatedData = $request->validated();
            $credentials = [
                'email' => $validatedData['email'],
                'password' => $validatedData['password']
            ];
            $rememberMe = $validatedData['remember_me'] ?? false;
            $authType = $validatedData['auth_type'] ?? 'hybrid';

            // Attempt authentication
            if (!Auth::attempt($credentials)) {
                $this->authService->handleFailedLogin($request, $validatedData['email']);
                throw ValidationException::withMessages([
                    'email' => 'Invalid email or password.'
                ]);
            }

            $user = Auth::user();
            $authData = $this->authService->createAuthData($request, $user, $rememberMe, $authType);

            $this->authService->updateUserLoginInfo($user, $request);
            $this->authService->logSuccessfulLogin($user, $request, $authData);
            $this->authService->clearRateLimit($request);


            return ApiResponse::success(
                'User logged in successfully.',
                new UserResource($user),
                $authData['meta']
            );
        } catch (Exception $e) {
            // Log the error with full context
            Log::error('User login failed with exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $request->input('email', 'unknown'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpException(500, 'Login failed. Please try again later.');
        }
    }


    public function storeWeb(UserLogInRequest $request): JsonResponse
    {
        try {
            // Rate limiting check
            $rateLimitKey = 'login_attempts:' . $request->ip();
            $this->authService->throttleLoginAttempts($rateLimitKey);

            // Extract validated data
            $validatedData = $request->validated();
            $credentials = [
                'email' => $validatedData['email'],
                'password' => $validatedData['password']
            ];
            $rememberMe = $validatedData['remember_me'] ?? false;
            $includeToken = $validatedData['include_token'] ?? false;

            // Attempt authentication
            if (!Auth::attempt($credentials, $rememberMe)) {
                $this->authService->handleFailedLogin($request, $validatedData['email']);
                throw ValidationException::withMessages([
                    'email' => 'Invalid email or password.'
                ]);
            }

            $user = Auth::user();

            // Create web session
            $sessionData = $this->authService->createWebSession($request, $user, $rememberMe);

            // Build meta data
            $meta = [
                'login_at' => now()->toISOString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'auth_type' => $includeToken ? 'hybrid' : 'web',
                'session_id' => $sessionData['session_id'],
                'session_expires_at' => $sessionData['expires_at'],
                'remember_me' => $rememberMe
            ];

            // Opciono dodaj API token
            if ($includeToken) {
                $tokenData = $this->createApiToken($request, $user, $rememberMe);
                $meta['api_token'] = $tokenData['token'];
                $meta['token_type'] = $tokenData['token_type'];
                $meta['token_expires_at'] = $tokenData['expires_at'];
            }

            $this->authService->updateUserLoginInfo($user, $request);
            $this->authService->clearRateLimit($request);

            // Log successful login
            Log::info('Web user logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'remember_me' => $rememberMe,
                'include_token' => $includeToken,
                'session_id' => $sessionData['session_id']
            ]);

            return ApiResponse::success(
                'User logged in successfully.',
                new UserResource($user),
                $meta
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            // Log the error with full context
            Log::error('Web user login failed with exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $request->input('email', 'unknown'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpException(500, 'Login failed. Please try again later.');
        }
    }

    /**
     * Logout user
     *
     * @OA\Post(
     *     path="/log-out",
     *     operationId="logoutUser",
     *     tags={"Authentication"},
     *     summary="User log out",
     *     description="Logs out the authenticated user and revokes their token",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         description="Logout options",
     *         @OA\JsonContent(
     *             @OA\Property(property="logout_all_devices", type="boolean", example=false, description="Revoke tokens from all devices"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User logged out successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="logged_out_at", type="string", format="date-time", example="2025-08-14T14:30:00.000000Z"),
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="logout_all_devices", type="boolean", example=false),
     *                 @OA\Property(property="tokens_revoked", type="integer", example=1)
     *             )
     *         )
     *     ),
     * )
     */
    public function destroyApi(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                throw new AuthenticationException();
            }

            $logoutAllDevices = $request->boolean('logout_all_devices', false);

            $isApiRequest = $request->is('api/*') ||
                $request->header('X-Auth-Method') === 'api' ||
                $request->header('Accept') === 'application/json' ||
                $request->wantsJson() ||
                $request->bearerToken();

            $tokensRevoked = 0;

            if ($isApiRequest) {
                // API Logout - Handle Sanctum tokens
                if ($logoutAllDevices) {
                    // Revoke all tokens for the user
                    $tokensRevoked = $user->tokens()->count();
                    $user->tokens()->delete();

                    // Clear remember token from database
                    $user->update(['remember_token' => null]);
                } else {
                    // Revoke only the current token
                    $currentToken = $user->currentAccessToken();
                    if ($currentToken) {
                        $currentToken->delete();
                        $tokensRevoked = 1;
                    }
                }
            } else {
                // Web Session Logout
                if ($logoutAllDevices) {
                    // Clear all sessions for user (if using database sessions)
                    if (config('session.driver') === 'database') {
                        \DB::table('sessions')
                            ->where('user_id', $user->id)
                            ->delete();
                    }

                    // Clear all tokens as well
                    $tokensRevoked = $user->tokens()->count();
                    $user->tokens()->delete();

                    // Clear remember token
                    $user->update(['remember_token' => null]);

                    // Clear remember me cookie
                    Cookie::queue(Cookie::forget(Auth::getRecallerName()));
                }

                // Always clear current session
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            // Update last logout timestamp
            $user->update([
                'last_logout_at' => now(),
                'last_logout_ip' => $request->ip(),
                'last_logout_user_agent' => $request->userAgent()
            ]);

            // Log successful logout
            Log::info('User logged out successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logout_all_devices' => $logoutAllDevices,
                'tokens_revoked' => $tokensRevoked
            ]);

            $meta = [
                'logout_all_devices' => $logoutAllDevices,
                'tokens_revoked' => $tokensRevoked
            ];

            return response()->json(
                ApiResponse::success(
                    'User logged out successfully.',
                    null,
                    $meta
                )
            );
        } catch (Exception $e) {
            // Log the error
            Log::error('User logout failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 'unknown',
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpException(500, 'Log out failed. Please try again later.');
        }
    }

    public function destroyWeb(Request $request): JsonResponse
    {
        try {
            $user = auth('web')->user();

            if (!$user) {
                throw new AuthenticationException();
            }

            $logoutAllDevices = $request->boolean('logout_all_devices', false);

            if ($logoutAllDevices) {
                // Clear all sessions for user (if using database sessions)
                if (config('session.driver') === 'database') {
                    \DB::table('sessions')
                        ->where('user_id', $user->id)
                        ->delete();
                }

                $user->tokens()->delete();

                // Clear remember token
                $user->update(['remember_token' => null]);

                // Clear remember me cookie
                Cookie::queue(Cookie::forget(auth('web')->getRecallerName()));
            }

            // Koristi web guard za logout
            auth('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Update last logout timestamp
            $user->update([
                'last_logout_at' => now(),
                'last_logout_ip' => $request->ip(),
                'last_logout_user_agent' => $request->userAgent()
            ]);

            // Log successful logout
            Log::info('User logged out successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logout_all_devices' => $logoutAllDevices
            ]);

            $meta = [
                'logout_all_devices' => $logoutAllDevices
            ];

            return response()->json(
                ApiResponse::success(
                    'User logged out successfully.',
                    null,
                    $meta
                )
            );
        } catch (Exception $e) {
            // Log the error
            Log::error('User logout failed', [
                'error' => $e->getMessage(),
                'user_id' => auth('web')->id() ?? 'unknown',
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpException(500, 'Log out failed. Please try again later.');
        }
    }
}
