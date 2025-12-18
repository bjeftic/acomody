<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;

class DashboardController
{
    public function index(Request $request)
    {
        return view('super-admin.dashboard.index');
    }
}
