<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class SetPasswordController
{
    /**
     * Show the set-password form (used for both invitation and reset flows).
     */
    public function show(Request $request): View
    {
        return view('super-admin.set-password', [
            'token' => $request->query('token', ''),
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Validate the token and update the superadmin's password.
     */
    public function store(Request $request): View|RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                User::withoutAuthorization(function () use ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                        'password_changed_at' => now(),
                    ])->save();
                });

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return view('super-admin.set-password', [
                'token' => '',
                'email' => '',
                'success' => true,
            ]);
        }

        return back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
    }
}
