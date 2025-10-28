<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = Cache::remember('faculties', 3600, function () {
            return \App\Models\Faculty::all();
        });

        return response()->json($faculties);
    }
}
