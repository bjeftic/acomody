<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Support\ApiResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        dd('TEST');
        $user = $request->user();

        if (!$user) {
            throw new AuthenticationException('User not authenticated.');
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
                'data' => [],
                'meta' => []
            ], 409);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully.',
                'data' => [],
                'meta' => []
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email verification failed.',
            'data' => [],
            'meta' => []
        ], 500);
    }

    /**
     * Verify email address
     */
    public function verify(Request $request, $id, $hash)
    {
        try {
            // Find user
            $user = User::findOrFail($id);

            if (!$user) {
                throw new AuthenticationException('User not authenticated.');
            }

            // Check if signature is valid
            if (!$request->hasValidSignature()) {
                return ApiResponse::error('Invalid verification link.', null, null, 401);
            }

            // Check if hash matches user's email
            if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
                return ApiResponse::error('Invalid verification link.', null, null, 401);
            }

            // Check if email is already verified
            if ($user->hasVerifiedEmail()) {
                return redirect('/email-verify');
                return ApiResponse::success('Email already verified.');
            }

            // Mark email as verified
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return redirect('/email-verify');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('User not found.', null, null, 404);
        } catch (\Exception $e) {
            Log::error('Email verification error: ' . $e->getMessage(), [
                'id' => $id,
                'hash' => $hash,
                'signature' => $request->query('signature'),
                'expires' => $request->query('expires'),
                'url' => $request->fullUrl(),
            ]);

            return ApiResponse::error('Email verification failed. Please try again later.', null, null, 500);
        }
    }

    /**
     * Send email verification notification
     */
    public function send(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::error('Email already verified.', null, null, 409);
        }

        $user->sendEmailVerificationNotification();

        return ApiResponse::success('Verification link sent.');
    }

    /**
     * Check verification status
     */
    public function status(Request $request)
    {
        $user = $request->user();

        $meta = [
            'verified' => $user->hasVerifiedEmail(),
            'email' => $user->email,
            'verification_sent_at' => $user->email_verified_at ? null : 'available'
        ];

        return ApiResponse::success(
            'Verification status retrieved successfully.',
            null,
            $meta
        );
    }
}
