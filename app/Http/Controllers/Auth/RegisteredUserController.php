<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserSignUpRequest;
use App\Http\Support\ApiResponse;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Auth\Events\Registered;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     *             required={"email","password","confirm_password"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="john.smith@example.com",
     *                 description="User email address"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 minLength=8,
     *                 example="password123",
     *                 description="User password"
     *             ),
     *             @OA\Property(
     *                 property="confirm_password",
     *                 type="string",
     *                 format="password",
     *                 example="password123",
     *                 description="Password confirmation"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User successfully registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User registered successfully. Please check your email to verify your account."
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(
     *                     property="verification_required",
     *                     type="boolean",
     *                     example=true,
     *                     description="Indicates if email verification is required"
     *                 ),
     *                 @OA\Property(
     *                     property="login_enabled",
     *                     type="boolean",
     *                     example=false,
     *                     description="Indicates if login is enabled before verification"
     *                 ),
     *                 @OA\Property(
     *                     property="verification_expires_at",
     *                     type="string",
     *                     format="date-time",
     *                     example="2025-08-01T10:30:00.000000Z",
     *                     description="Expiration time of the verification link"
     *                 )
     *             )
     *         )
     *     )
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
                return ApiResponse::rateLimitExceeded('Too many registration attempts. Please try again later.', 300);
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

            $meta = [
                'verification_required' => true,
                'login_enabled' => false,
                'verification_expires_at' => now()->addHours(24)->toISOString()
            ];

            return ApiResponse::success(
                'User registered successfully. Please check your email to verify your account.',
                null,
                $meta,
                201
            );
        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? 'unknown',
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpException(500, 'Registration failed. Please try again later.');
        }
    }
}
