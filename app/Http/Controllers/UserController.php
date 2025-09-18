<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     *                      property="user",
     *                      type="object",
     *                      ref="#/components/schemas/User"
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
     *     )
     * )
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                throw new AuthenticationException('User not authenticated.');
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

            $meta = [
                'email_verified' => $user->hasVerifiedEmail(),
                'profile_complete' => $profileComplete,
                'account_status' => $user->status ?? 'active'
            ];

            return ApiResponse::success(
                'User retrieved successfully.',
                new UserResource($user),
                $meta
            );
        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve user profile', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 'unknown',
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpException(500, 'Failed to retrieve user profile. Please try again later.');
        }
    }
}
