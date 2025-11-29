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

    /**
     * Detail firmy pre FE – používa sa po vybraní firmy v autocomplete.
     *
     * GET /api/companies/{company}
     */
    public function show(Company $company)
    {
        $company->load([
            'address.country',
            'ownerProfiles.user',
        ]);

        $address      = $company->address;
        $country      = $address?->country;
        $ownerProfile = $company->ownerProfiles;
        $contactUser  = $ownerProfile?->user;

        return response()->json([
            'id'          => $company->id,
            'name'        => $company->name,
            'description' => $company->description,
            'website'     => $company->website,

            'address'     => $address ? [
                'city'         => $address->city,
                'street'       => $address->street,
                'house_number' => $address->house_number,
                'postal_code'  => $address->postal_code,
                'country'      => $country ? [
                    'id'   => $country->id,
                    'name' => $country->name,
                    'icon' => $country->icon,
                ] : null,
            ] : null,

            'contact_person' => $contactUser ? [
                'id'              => $contactUser->id,
                'first_name'      => $contactUser->first_name,
                'last_name'       => $contactUser->last_name,
                'title_before'    => $contactUser->title_before,
                'title_after'     => $contactUser->title_after,
                'email'           => $contactUser->email,
                'phone_number'    => $contactUser->phone_number,   // <-- PRIDANÉ
                'role_at_company' => $ownerProfile?->role_at_company,
            ] : null,
        ]);
    }
}
