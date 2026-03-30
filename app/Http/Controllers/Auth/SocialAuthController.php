<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected SubscriptionService $subscriptionService,
    ) {}

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $frontendUrl = config('app.frontend_url', config('app.url'));

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('Google OAuth callback failed', ['error' => $e->getMessage()]);

            return redirect($frontendUrl.'/?social_error=google_failed');
        }

        try {
            DB::beginTransaction();

            $user = $this->findOrCreateGoogleUser($googleUser, $request);

            Auth::login($user, remember: true);

            $this->authService->updateUserLoginInfo($user, $request);

            Log::info('User logged in via Google', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Google OAuth user handling failed', ['error' => $e->getMessage()]);

            return redirect($frontendUrl.'/?social_error=google_failed');
        }

        return redirect($frontendUrl.'/');
    }

    private function findOrCreateGoogleUser(object $googleUser, Request $request): User
    {
        // Find by google_id first
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            return $user;
        }

        // Find by email and link account
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            User::withoutAuthorization(fn () => $user->update(['google_id' => $googleUser->getId()]));

            return $user;
        }

        // Create new user
        $user = User::create([
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'email_verified_at' => now(),
            'terms_accepted_at' => now(),
            'privacy_policy_accepted_at' => now(),
            'registration_ip' => $request->ip(),
            'last_login_user_agent' => $request->userAgent(),
        ]);

        $this->subscriptionService->assignFreePlan($user);

        if ($this->subscriptionService->isColdStartActive()) {
            $this->subscriptionService->markAsEarlyHost($user);
        }

        return $user;
    }
}
