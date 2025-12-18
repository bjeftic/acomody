<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function logout(Request $request)
    {
        $user = userOrFail();
        $this->authService->logout($user, $request);
        return redirect('/');
    }
}
