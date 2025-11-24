<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function searchByName(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if ($q === '') {
            return response()->json([
                'message' => 'Query parameter "q" is required.',
            ], 422);
        }

        $pattern = '%' . str_replace(' ', '%', $q) . '%';

        $companies = Company::query()
            ->where('name', 'LIKE', $pattern)
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);

        return response()->json($companies);
    }
}
