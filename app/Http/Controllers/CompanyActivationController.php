<?php

namespace App\Http\Controllers;

use App\Models\CompanyActivation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CompanyActivationController extends Controller
{
    public function activate(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json(['message' => 'Token chýba.'], 400);
        }

        $activation = CompanyActivation::where('hash', $token)->first();

        if (!$activation) {
            return response()->json(['message' => 'Neplatný alebo neexistujúci token.'], 404);
        }

        if ($activation->consumed_at) {
            return response()->json(['message' => 'Token už bol použitý.'], 400);
        }

        if ($activation->created_at->lt(Carbon::now()->subDay())) {
            return response()->json(['message' => 'Aktivačný token expiroval.'], 400);
        }

        $ownerProfile = \App\Models\CompanyOwnerProfile::where('company_activation_id', $activation->id)->first();

        if (!$ownerProfile) {
            return response()->json(['message' => 'K aktivácii nebol nájdený profil firmy.'], 404);
        }

        $company = $ownerProfile->company;
        $user = $ownerProfile->user;

        if ($company) {
            $company->active = true;
            $company->save();
        }

        if ($user) {
            $user->active = true;
            $user->save();
        }

        $ownerProfile->is_active = true;
        $ownerProfile->save();

        $activation->update(['consumed_at' => Carbon::now()]);

        return response()->json(['message' => 'Účet bol úspešne aktivovaný.']);
    }
}
