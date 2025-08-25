<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Get authenticated user profile
     *
     * @OA\Get(
     *     path="/user",
     *     operationId="getUserProfile",
     *     tags={"User"},
     *     summary="Get authenticated user profile",
     *     description="Returns the profile information of the currently authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User profile retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="user_id", type="integer", example=123),
     *                     @OA\Property(property="email", type="string", example="john.smith@example.com"),
     *                     @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-07-31T10:30:00.000000Z"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T08:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-14T14:30:00.000000Z"),
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="email_verified", type="boolean", example=true),
     *                 @OA\Property(property="profile_complete", type="boolean", example=true),
     *                 @OA\Property(property="account_status", type="string", example="active")
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
     *         description="Server error retrieving user profile",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve user profile. Please try again later."),
     *             @OA\Property(property="error_code", type="string", example="PROFILE_RETRIEVAL_FAILED")
     *         )
     *     ),
     * )
     */
    public function show(Request $request): JsonResponse
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

            // Log profile access
            Log::info('User profile accessed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Check if profile is complete (adjust fields as needed)
            $profileComplete = !empty($user->userProfile->first_name) &&
                !empty($user->userProfile->last_name) &&
                !empty($user->userProfile->phone);

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully.',
                'data' => [
                    'user' => [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at?->toISOString(),
                        'created_at' => $user->created_at->toISOString(),
                        'updated_at' => $user->updated_at->toISOString(),
                    ]
                ],
                'meta' => [
                    'email_verified' => $user->hasVerifiedEmail(),
                    'profile_complete' => $profileComplete,
                    'account_status' => $user->status ?? 'active' // Adjust based on your User model
                ]
            ], 200);
        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve user profile', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 'unknown',
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user profile. Please try again later.',
                'error_code' => 'PROFILE_RETRIEVAL_FAILED'
            ], 500);
        }
    }
}
