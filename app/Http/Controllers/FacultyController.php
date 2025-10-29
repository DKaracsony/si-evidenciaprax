<?php

namespace App\Http\Controllers;

use App\Services\FacultyService;
use Illuminate\Http\JsonResponse;

class FacultyController extends Controller
{
    public function __construct(private FacultyService $facultyService) {}

    public function index(): JsonResponse
    {
        return response()->json($this->facultyService->all());
    }
}
