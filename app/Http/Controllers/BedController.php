<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use Illuminate\Http\JsonResponse;

class BedController extends Controller
{
    public function index(): JsonResponse
    {
        $beds = Bed::where('is_active', true)->orderBy('name')->get();

        return response()->json(['data' => $beds]);
    }
}
