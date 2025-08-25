<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserLogInRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;



class AuthenticatedSessionController extends Controller
{
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
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="john.smith@example.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8, example="SecurePass123!"),
     *             @OA\Property(property="remember_me", type="boolean", example=false),
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
     *                 @OA\Property(property="token", type="string", example="2|abcdef123456789..."),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2025-08-21T14:30:00.000000Z")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="remember_me", type="boolean", example=false),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="Email address is required.")),
     *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="Password is required.")),
     *               )
     *           )
     *       ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentication failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid email or password."),
     *             @OA\Property(property="error_code", type="string", example="INVALID_CREDENTIALS")
     *         )
     *     ),

     *     @OA\Response(
     *         response=429,
     *         description="Too many login attempts",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Too many login attempts. Please try again later."),
     *             @OA\Property(property="retry_after", type="integer", example=900)
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
            $maxAttempts = config('auth.login_rate_limit', 10);

            if ($attempts >= $maxAttempts) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many login attempts. Please try again later.',
                    'retry_after' => 900 // 15 minutes
                ], 429);
            }

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

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                    'error_code' => 'INVALID_CREDENTIALS'
                ], 401);
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

            return response()->json([
                'success' => true,
                'message' => 'User logged in successfully.',
                'data' => $authData,
                'meta' => [
                    'login_at' => now()->toISOString(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            ], 200);
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

            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again later.',
                'error_code' => 'LOGIN_FAILED',
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    public function storeWeb(UserLogInRequest $request): JsonResponse
    {
        try {
            // Rate limiting check
            $rateLimitKey = 'login_attempts:' . $request->ip();
            $attempts = cache()->get($rateLimitKey, 0);
            $maxAttempts = config('auth.login_rate_limit', 10);

            if ($attempts >= $maxAttempts) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many login attempts. Please try again later.',
                    'retry_after' => 900 // 15 minutes
                ], 429);
            }

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

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                    'error_code' => 'INVALID_CREDENTIALS'
                ], 401);
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

            $authData = [
                'session_id' => $request->session()->getId(),
                'expires_at' => $expiresAt->toISOString(),
                'remember_me' => $rememberMe
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

            return response()->json([
                'success' => true,
                'message' => 'User logged in successfully.',
                'data' => $authData,
                'meta' => [
                    'login_at' => now()->toISOString(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            ], 200);
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

            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again later.',
                'error_code' => 'LOGIN_FAILED',
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
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
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *             @OA\Property(property="error_code", type="string", example="UNAUTHENTICATED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error during logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Logout failed. Please try again later."),
     *             @OA\Property(property="error_code", type="string", example="LOGOUT_FAILED")
     *         )
     *     ),
     * )
     */
    public function destroyApi(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
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

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully.',
                'data' => [
                    'logged_out_at' => now()->toISOString(),
                ],
                'meta' => [
                    'logout_all_devices' => $logoutAllDevices,
                    'tokens_revoked' => $tokensRevoked
                ]
            ], 200);
        } catch (Exception $e) {
            // Log the error
            Log::error('User logout failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 'unknown',
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again later.',
                'error_code' => 'LOGOUT_FAILED'
            ], 500);
        }
    }

    public function destroyWeb(Request $request): JsonResponse
    {
        try {
            $user = auth('web')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
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

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully.',
                'data' => [
                    'logged_out_at' => now()->toISOString(),
                ],
                'meta' => [
                    'logout_all_devices' => $logoutAllDevices,
                    'tokens_revoked' => $tokensRevoked ?? 0
                ]
            ], 200);
        } catch (Exception $e) {
            // Log the error
            Log::error('User logout failed', [
                'error' => $e->getMessage(),
                'user_id' => auth('web')->id() ?? 'unknown',
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again later.',
                'error_code' => 'LOGOUT_FAILED'
            ], 500);
        }
    }
}
