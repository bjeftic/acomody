<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): JsonResponse
    {
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
}
