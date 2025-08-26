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

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(NewPasswordRequest $request): JsonResponse
    {
        // Attempt to reset the user's password
        $status = Password::reset(
            $request->validated(),
            function ($user) use ($request) {
                $password = $request->password;

                if (!is_string($password)) {
                    throw ValidationException::withMessages(['password' => ['Invalid password provided.']]);
                }

                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Ensure $status is a string or throw an exception if it's invalid
        if (!is_string($status)) {
            throw ValidationException::withMessages([
                'email' => ['An unknown error occurred during password reset.'],
            ]);
        }

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                    'success' => true,
                    'message' => 'Password reset successfully.',
                    'data' => [
                        'status' => __($status),
                    ],
                    'meta' => [
                        'email' => $user->email,
                    ]
                ], 200);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
