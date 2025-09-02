<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\NewPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Services\AuthService;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Support\ApiResponse;

class NewPasswordController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    /**
     * @OA\Post(
     *     path="/reset-password",
     *     operationId="resetPassword",
     *     tags={"Authentication"},
     *     summary="Reset user password",
     *     description="Reset user password using a valid token received via email. Token can only be used once.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Password reset data",
     *         @OA\JsonContent(ref="#/components/schemas/NewPasswordRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset successfully."),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="email", type="string", example="user@example.com")
     *             )
     *         )
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
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid password provided.")
     *         )
     *     ),
     *          security={
     *         {"throttle": {"password-reset"}}
     *     }
     * )
     */
    public function store(NewPasswordRequest $request): JsonResponse
    {
        $this->authService->throttlePasswordResetAttempts($request);

        // Extract validated data
        $validatedData = $request->validated();

        Log::info('Password reset attempt', [
            'email' => $validatedData['email'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $passwordHistoryCheck = $this->authService->checkPasswordHistory($user, $validatedData['password']);

        if ($passwordHistoryCheck) {
            return $passwordHistoryCheck;
        }

        // Attempt to reset the user's password
        $status = Password::reset(
            $validatedData,
            function ($user) use ($request, $validatedData) {
                $hashedPassword = Hash::make($validatedData['password']);

                // Update user with new password and security measures
                $user->forceFill([
                    'password' => $hashedPassword,
                    'remember_token' => Str::random(60),
                    'password_changed_at' => now(),
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                ])->save();

                // Revoke all existing tokens for security
                $this->authService->revokeUserTokens($user);

                // Store password hash for history checking
                $this->authService->storePasswordHistory($user, $hashedPassword);

                Log::info('Password reset successful', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now(),
                ]);

                // Dispatch events
                event(new PasswordReset($user));
            }
        );

        $meta = [
            'status' => __($status)
        ];

        // Handle response
        if ($status === Password::PASSWORD_RESET) {
            return ApiResponse::success(
                'Password reset successfully.',
                null,
                $meta
            );
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
