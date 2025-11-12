<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectSuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only redirect if:
        // 1. User is authenticated
        // 2. User is super admin
        // 3. Trying to access root or Vue app routes
        if (auth()->check() &&
            auth()->user()->is_superadmin &&
            !$request->is('admin/*') &&
            !$request->is('api/*') &&
            !$request->is('log-in') &&
            !$request->is('log-out')) {

            return redirect()->route('admin.dashboard.index');
        }

        return $next($request);
    }
}
