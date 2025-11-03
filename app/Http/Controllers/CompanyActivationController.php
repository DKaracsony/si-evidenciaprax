<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\MailSender;

class CompanyActivationController extends Controller
{
    public function activate(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required','string'],
            'email' => ['required','email'],
        ]);

        $token = $request->query('token');
        $email = $request->query('email');
        $tokenHash = hash('sha256', $token);

        $rec = DB::table('company_activations')
            ->where('hash', $tokenHash)
            ->where('sent_to_mail', $email)
            ->orderByDesc('id')
            ->first();

        if (!$rec) {
            return response()->json(['code'    => 'TOKEN_INVALID', 'message' => 'Aktivačný odkaz je neplatný.'], 422);
        }


        $expiresAt = Carbon::parse($rec->created_at)->addHours(48);
        if (now()->greaterThan($expiresAt)) {
            return response()->json(['code'    => 'TOKEN_EXPIRED', 'message' => 'Platnosť aktivačného odkazu vypršala.'], 422);
        }


        if (!is_null($rec->consumed_at)) {
            return response()->json(['code'    => 'ALREADY_ACTIVATED', 'message' => 'Účet už bol aktivovaný.'], 422);
        }


        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['message' => 'Používateľ s danou e-mailovou adresou neexistuje.'], 422);
        }


        $temporaryPassword = Str::password(14);

        DB::transaction(function () use ($user, $temporaryPassword, $rec) {
            $user->password_hash = Hash::make($temporaryPassword);
            $user->active = true;
            $user->password_reset_needed = true;
            $user->save();

            DB::table('company_activations')
                ->where('id', $rec->id)
                ->update(['consumed_at' => now()]);
        });


        (new MailSender(
            'company_temp_password',
            [$user->email],
            [
                'user'              => $user,
                'temporaryPassword' => $temporaryPassword,
                'loginUrl'          => rtrim(url('/login'), '/'),
            ]
        ))->send();


        $payload = [
            'message'   => 'Účet bol úspešne aktivovaný. Teraz sa môžete prihlásiť.',
            'login_url' => rtrim(url('/login'), '/'),
        ];
        if (config('app.debug')) {
            $payload['temporary_password'] = $temporaryPassword;
        }

        return response()->json($payload, 200);

    }
}
