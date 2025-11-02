<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\CompanyTempPasswordMail;

class CompanyActivationController extends Controller
{
    public function activate(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        return response()->json(['message' => 'Company activated'], 200);

        /*$tokenHash = hash('sha256', $request->query('token'));
        $email = $request->query('email');

        $rec = DB::table('company_activations')
            ->where('hash', $tokenHash)
            ->where('sent_to_mail', $email)
            ->first();

        if (!$rec) {
            return response()->json(['message' => 'Aktivačný odkaz je neplatný.'], 410);
        }

        $expiresAt = Carbon::parse($rec->created_at)->addHours(48);
        if (now()->greaterThan($expiresAt)) {
            return response()->json(['message' => 'Platnosť aktivačného odkazu vypršala. Požiadajte o nový odkaz na prihlasovacej stránke.'], 410);
        }

        if (!is_null($rec->consumed_at)) {
            return response()->json(['message' => 'Tento aktivačný odkaz už bol použitý. Prihláste sa alebo požiadajte o nový odkaz.'], 410);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['message' => 'Používateľ s danou e-mailovou adresou neexistuje.'], 410);
        }

        $temporaryPassword = Str::password(14);

        $user->password_hash = Hash::make($temporaryPassword);
        $user->active = true;
        $user->save();

        DB::table('company_activations')->where('id', $rec->id)->update([
            'consumed_at' => now(),
        ]);

        Mail::to($user->email)->send(new CompanyTempPasswordMail(
            kontaktMeno: $user->first_name,
            docasneHeslo: $temporaryPassword,
            prihlasenieUrl: config('app.front_login_url')
        ));

        return response()->json([
            'message' => 'Účet bol úspešne aktivovaný. Teraz sa môžete prihlásiť.',
            'temporary_password' => $temporaryPassword,
            'login_url' => config('app.front_login_url'),
        ]);*/
    }
}
