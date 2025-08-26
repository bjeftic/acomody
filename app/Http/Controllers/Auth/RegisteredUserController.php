<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
UserPasswordResetRequestUserSignUpRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Register a new user
     *
     * @OA\Post(
     *     path="/sign-up",
     *     operationId="signUpUser",
     *     tags={"Authentication"},
     *     summary="Sign up a new user",
     *     description="Creates a new user account with email verification",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User registration data",
     *         @OA\JsonContent(
     *             required={"email","password","terms_accepted_at","privacy_policy_accepted_at"},
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="john.smith@example.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8, example="SecurePass123!"),
     *             @OA\Property(property="confirm_password", type="string", format="password", minLength=8, example="SecurePass123!"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User successfully registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered successfully. Please check your email to verify your account."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user_id", type="integer", example=123),
     *                 @OA\Property(property="email", type="string", example="john.smith@example.com"),
     *                 @OA\Property(property="email_verified_at", type="string", nullable=true, example=null),
     *                 @OA\Property(property="terms_accepted_at", type="string", format="date-time", example="2025-07-31T10:30:00.000000Z"),
     *                 @OA\Property(property="privacy_policy_accepted_at", type="string", format="date-time", example="2025-07-31T10:30:00.000000Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-31T10:30:00.000000Z")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="verification_required", type="boolean", example=true),
     *                 @OA\Property(property="login_enabled", type="boolean", example=false),
     *                 @OA\Property(property="verification_expires_at", type="string", format="date-time", example="2025-08-01T10:30:00.000000Z")
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
     *                 @OA\Property(property="terms_accepted", type="array", @OA\Items(type="string", example="You must accept the terms and conditions.")),
     *                 @OA\Property(property="privacy_policy_accepted", type="array", @OA\Items(type="string", example="You must accept the privacy policy.")),
     *               )
     *           )
     *       ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many registration attempts",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Too many registration attempts. Please try again later."),
     *             @OA\Property(property="retry_after", type="integer", example=300)
     *         )
     *     ),
     * )
     */
    public function signUp(UserSignUpRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Rate limiting check
            $rateLimitKey = 'registration_attempts:' . $request->ip();
            $attempts = cache()->get($rateLimitKey, 0);

            if ($attempts >= config('auth.registration_rate_limit', 5)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many registration attempts. Please try again later.',
                    'retry_after' => 300
                ], 429);
            }

            // Increment rate limit counter
            cache()->put($rateLimitKey, $attempts + 1, now()->addMinutes(5));

            // Extract validated data
            $validatedData = $request->validated();

            // Create user with validated data
            $userData = [
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'email_verification_token' => Str::random(64),
                'terms_accepted_at' => now(),
                'privacy_policy_accepted_at' => now(),
                'registration_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            $user = User::create($userData);

            // Send email verification
            event(new Registered($user));

            // Log successful registration
            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            // Clear rate limit on successful registration
            cache()->forget($rateLimitKey);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. Please check your email to verify your account.',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'terms_accepted_at' => $user->terms_accepted_at,
                    'privacy_policy_accepted_at' => $user->privacy_policy_accepted_at,
                    'created_at' => $user->created_at->toISOString(),
                ],
                'meta' => [
                    'verification_required' => true,
                    'login_enabled' => false,
                    'verification_expires_at' => now()->addHours(24)->toISOString()
                ]
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? 'unknown',
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again later.',
                'error_code' => 'REGISTRATION_FAILED'
            ], 500);
        }
    }
}
