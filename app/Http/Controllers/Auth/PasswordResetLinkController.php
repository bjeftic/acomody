<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\User\UserPasswordResetRequest;

class PasswordResetLinkController extends Controller
{
    /**
     * @OA\Post(
     *     path="/forgot-password",
     *     summary="Request password reset link",
     *     description="Send a password reset link to the user's email address. Returns success message regardless of whether email exists (to prevent email enumeration attacks).",
     *     operationId="requestPasswordReset",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Email address for password reset",
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="john.smith@example.com",
     *                 description="User email address"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset request processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true,
     *                 description="Request success status"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="If that email address is in our system, we have emailed a password reset link.",
     *                 description="Generic success message for security"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="status",
     *                     type="string",
     *                     example="passwords.sent",
     *                     description="Password reset status (only when email exists)"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     example="user@example.com",
     *                     description="Email address (only when email exists)"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(UserPasswordResetRequest $request): JsonResponse
    {
        $email = $request->input('email');

        // Log the attempt
        Log::info('Password reset requested for email: ' . $email);

        $user = User::where('email', $email)->first();
        if (!$user) {
            Log::warning('Password reset requested for non-existent email: ' . $email);

            // Still return success to prevent email enumeration
            return response()->json([
                'success' => true,
                'message' => 'If that email address is in our system, we have emailed a password reset link.',
                'data' => [],
                'meta' => []
            ], 200);
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        try {
            // Attempt to send the password reset link
            $status = Password::sendResetLink(
                $request->only('email')
            );

            Log::info('Password reset status: ' . $status . ' for email: ' . $email);

            if ($status === Password::RESET_LINK_SENT) {
                Log::info('Password reset link sent successfully to: ' . $email);

                return response()->json([
                    'success' => true,
                    'message' => 'User logged out successfully.',
                    'data' => [
                        'status' => __($status),
                    ],
                    'meta' => [
                        'email' => $email,
                    ]
                ], 200);
            }

            // Log the failure reason
            Log::error('Password reset failed with status: ' . $status . ' for email: ' . $email);

            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        } catch (\Exception $e) {
            // Log any exceptions
            Log::error('Password reset exception: ' . $e->getMessage(), [
                'email' => $email,
                'trace' => $e->getTraceAsString()
            ]);

            throw ValidationException::withMessages([
                'email' => ['Failed to send reset link. Please try again later.'],
            ]);
        }
    }
}
