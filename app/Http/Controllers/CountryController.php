<?php

namespace App\Http\Controllers;

use App\Services\CountryService;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    public function __construct(private CountryService $countryService) {}

    /**
     * Return all countries (cached).
     */
    public function index(): JsonResponse
    {
        return response()->json($this->countryService->all());
    }
}
