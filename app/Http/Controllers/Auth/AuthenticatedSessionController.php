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
     *         @OA\JsonContent(ref="#/components/schemas/UserLogInRequest")
     *     ),
     *     @OA\Response(
     *     response=200,
     *     description="User successfully logged in",
     *     @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="User logged in successfully."),
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             @OA\Property(ref="#/components/schemas/UserResource")
     *         ),
     *         @OA\Property(
     *             property="meta",
     *             type="object",
     *             @OA\Property(property="login_at", type="string", format="date-time", example="2025-08-31T21:47:00.911232Z"),
     *             @OA\Property(property="ip_address", type="string", example="172.19.0.1"),
     *             @OA\Property(property="user_agent", type="string", example="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36")
     *         )
     *     )
     *     ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation failed",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="error",
     *                  ref="#/components/schemas/ValidationErrorResponse"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Authentication failed",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="error",
     *                  ref="#/components/schemas/AuthenticationErrorResponse"
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many login attempts",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Too many login attempts. Please try again later.")
     *         )
     *     ),
     * )
     */
    public function storeApi(UserLogInRequest $request): JsonResponse
    {
        try {
            // Rate limiting check
            $rateLimitKey = 'login_attempts:' . $request->ip();
            $attempts = cache()->get($rateLimitKey, 0);

            $this->authService->throttleLoginAttempts($rateLimitKey);

            // Extract validated data
            $validatedData = $request->validated();
            $credentials = [
                'email' => $validatedData['email'],
                'password' => $validatedData['password']
            ];
            $rememberMe = $validatedData['remember_me'] ?? false;

            // Attempt authentication
            if (!Auth::attempt($credentials)) {

                // Log failed login attempt
                Log::warning('Failed login attempt', [
                    'email' => $validatedData['email'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'attempts' => $attempts + 1
                ]);

                return ApiResponse::unauthorized(
                    'Invalid email or password.'
                );
            }

            $user = Auth::user();

            // API Authentication - use Sanctum tokens
            $tokenName = $rememberMe ? 'remember-api-token' : 'session-api-token';
            $tokenExpiration = $rememberMe ? now()->addDays(30) : now()->addHours(8);

            // Delete existing tokens of the same type for this user
            $user->tokens()->where('name', $tokenName)->delete();

            // Create new token
            $token = $user->createToken($tokenName, ['*'], $tokenExpiration);

            // Add metadata to token if supported
            if (method_exists($token->accessToken, 'update')) {
                $token->accessToken->update([
                    'metadata' => json_encode([
                        'remember_me' => $rememberMe,
                        'login_method' => 'api_token',
                        'created_via' => 'login',
                        'user_agent' => $request->userAgent(),
                        'ip_address' => $request->ip(),
                        'created_at' => now()->toISOString()
                    ])
                ]);
            }

            $authData = [
                'token' => $token->plainTextToken,
                'expires_at' => $tokenExpiration->toISOString(),
            ];

            // Update user login information
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'last_login_user_agent' => $request->userAgent(),
            ]);

            // Log successful login
            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'remember_me' => $rememberMe,
                'session_id' => $authData['session_id'] ?? null,
                'token_expires_at' => $authData['expires_at'] ?? null
            ]);

            // Clear rate limit on successful login
            cache()->forget($rateLimitKey);

            $meta = [
                'login_at' => now()->toISOString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            return ApiResponse::success(
                'User logged in successfully.',
                new UserResource($user),
                $meta
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
            $attempts = cache()->get($rateLimitKey, 0);

            $this->authService->throttleLoginAttempts($rateLimitKey);

            // Extract validated data
            $validatedData = $request->validated();
            $credentials = [
                'email' => $validatedData['email'],
                'password' => $validatedData['password']
            ];
            $rememberMe = $validatedData['remember_me'] ?? false;

            // Attempt authentication
            if (!Auth::attempt($credentials)) {
                // Increment rate limit counter for failed attempts
                cache()->put($rateLimitKey, $attempts + 1, now()->addMinutes(15));

                // Log failed login attempt
                Log::warning('Failed login attempt', [
                    'email' => $validatedData['email'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'attempts' => $attempts + 1
                ]);

                return ApiResponse::unauthorized(
                    'Invalid email or password.'
                );
            }

            $user = Auth::user();

            $request->session()->regenerate();

            // Handle remember me functionality
            if ($rememberMe) {
                $rememberToken = Str::random(60);
                $user->setRememberToken($rememberToken);
                $user->save();

                // Set remember me cookie with proper security flags
                Cookie::queue(Cookie::make(
                    Auth::getRecallerName(),
                    $user->id . '|' . $rememberToken,
                    config('auth.expire', 525600), // 1 year in minutes
                    config('session.path', '/'),
                    config('session.domain'),
                    config('session.secure', false),
                    true // httpOnly
                ));
            }

            $sessionLifetime = config('session.lifetime', 120); // minutes
            $expiresAt = $rememberMe
                ? now()->addYear()
                : now()->addMinutes($sessionLifetime);

            // Update user login information
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'last_login_user_agent' => $request->userAgent(),
            ]);

            $meta = [
                'session_id' => $request->session()->getId(),
                'expires_at' => $expiresAt->toISOString(),
                'remember_me' => $rememberMe
            ];

            // Log successful login
            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'remember_me' => $rememberMe,
                'session_id' => $authData['session_id'] ?? null,
                'token_expires_at' => $authData['expires_at'] ?? null
            ]);

            // Clear rate limit on successful login
            cache()->forget($rateLimitKey);

            return ApiResponse::success(
                'User logged in successfully.',
                new UserResource($user),
                $meta
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
     *      @OA\Response(
     *          response=401,
     *          description="Authentication failed",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="error",
     *                  ref="#/components/schemas/AuthenticationErrorResponse"
     *              )
     *          )
     *      ),
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

                // Clear all tokens as well
                $tokensRevoked = $user->tokens()->count();
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
                'logout_all_devices' => $logoutAllDevices,
                'tokens_revoked' => $tokensRevoked ?? 0
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
                'user_id' => auth('web')->id() ?? 'unknown',
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpException(500, 'Log out failed. Please try again later.');
        }
    }
}
