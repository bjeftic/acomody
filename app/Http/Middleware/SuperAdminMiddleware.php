<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check authentication
        if (!auth()->check()) {
            Log::warning('Unauthenticated admin access attempt', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            return redirect('/')
                ->with('error', 'Please log in to access this area.');
        }

        $user = auth()->user();

        // Check super admin status
        if (!$user->is_superadmin) {
            Log::warning('Non-super-admin attempted admin access', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return redirect('/page-not-found');
        }

        return $next($request);
    }
}
