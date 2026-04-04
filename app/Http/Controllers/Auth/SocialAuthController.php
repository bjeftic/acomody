<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SyncGoogleAvatarJob;
use App\Models\User;
use App\Services\AuthService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        $driver = Socialite::driver('google');

        if (app()->isLocal()) {
            $driver = $driver->stateless();
        }

        return $driver->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $frontendUrl = config('app.frontend_url', config('app.url'));

        try {
            $driver = Socialite::driver('google');

            if (app()->isLocal()) {
                $driver = $driver->stateless();
            }

            $googleUser = $driver->user();
        } catch (Throwable $e) {
            Log::warning('Google OAuth callback failed', ['error' => $e->getMessage()]);

            return redirect($frontendUrl.'/?social_error=google_failed');
        }

        try {
            DB::beginTransaction();

            $user = $this->findOrCreateGoogleUser($googleUser, $request);
            $isNewUser = $user->wasRecentlyCreated;

            // On local dev, the OAuth callback runs on localhost:8000 while the SPA runs on
            // acomody.local:8000 — different domains mean the session cookie won't carry over.
            // Use a short-lived token so the frontend can establish the session on the right domain.
            // Google data is stored in the cache so the profile can be created after Auth::login.
            if (app()->isLocal()) {
                DB::commit();

                $token = Str::random(64);
                Cache::put("google_oauth_token:{$token}", [
                    'user_id' => $user->id,
                    'is_new_user' => $isNewUser,
                    'first_name' => $googleUser->user['given_name'] ?? null,
                    'last_name' => $googleUser->user['family_name'] ?? null,
                    'avatar_url' => $googleUser->getAvatar(),
                ], now()->addMinutes(5));

                return redirect($frontendUrl.'/?oauth_token='.$token);
            }

            Auth::login($user, remember: true);

            if ($isNewUser) {
                $user->userProfile()->create([
                    'user_id' => $user->id,
                    'first_name' => $googleUser->user['given_name'] ?? null,
                    'last_name' => $googleUser->user['family_name'] ?? null,
                ]);

                if ($googleUser->getAvatar()) {
                    SyncGoogleAvatarJob::dispatch($user->id, $googleUser->getAvatar());
                }
            }

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

    public function exchangeGoogleToken(Request $request): JsonResponse
    {
        $token = $request->string('token');
        $data = Cache::pull("google_oauth_token:{$token}");

        if (! $data) {
            return response()->json(['success' => false, 'error' => ['message' => 'Invalid or expired token']], 422);
        }

        $user = User::findOrFail($data['user_id']);
        Auth::login($user, remember: true);

        if ($data['is_new_user']) {
            $user->userProfile()->create([
                'user_id' => $user->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
            ]);

            if ($data['avatar_url']) {
                SyncGoogleAvatarJob::dispatch($user->id, $data['avatar_url']);
            }
        }

        $this->authService->updateUserLoginInfo($user, $request);

        Log::info('User logged in via Google', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return response()->json(['success' => true]);
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

        // Create new user — profile creation happens after Auth::login so canBeCreatedBy passes
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
