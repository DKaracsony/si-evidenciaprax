<?php

namespace App\Http\Controllers;

use App\Models\CompanyActivation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyActivationController extends Controller
{
    public function activate(Request $request)
    {
        $tokenPlain = $request->query('token');

        if (!$tokenPlain) {
            return response()->json(['message' => 'Token chýba.'], 400);
        }

        $activation = DB::transaction(function () use ($tokenPlain) {

            $candidates = CompanyActivation::whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->get();

            return $candidates->first(fn($t) => Hash::check($tokenPlain, $t->hash));
        });

        if (!$activation) {
            return response()->json(['message' => 'Neplatný alebo expirovaný token.'], 410);
        }

        if ($activation->consumed_at !== null) {
            return response()->json(['message' => 'Účet už bol aktivovaný.'], 200);
        }

        $ownerProfile = $activation->ownerProfile;

        if (!$ownerProfile) {
            return response()->json(['message' => 'Nenájdený profil firmy pre tento token.'], 404);
        }

        $company = $ownerProfile->company;
        $user = $ownerProfile->user;

        if ($company) {
            $company->update(['active' => true]);
        }

        if ($user) {
            $user->update(['active' => true]);
        }

        $ownerProfile->update(['is_active' => true]);

        $activation->update(['consumed_at' => now()]);

        CompanyActivation::where('company_id', $activation->company_id)
            ->where('id', '!=', $activation->id)
            ->update([
                'revoked_at' => now(),
            ]);

        return response()->json(['message' => 'Účet bol úspešne aktivovaný.'], 200);
    }
}
