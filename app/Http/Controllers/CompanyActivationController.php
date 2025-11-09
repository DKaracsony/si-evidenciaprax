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
use App\Models\CompanyActivation;
use App\Models\CompanyOwnerProfile; // ⬅️ added

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
            return response()->json(['code' => 'TOKEN_INVALID', 'message' => 'Aktivačný odkaz je neplatný.'], 422);
        }

        $expiresAt = Carbon::parse($rec->created_at)->addHours(48);
        if (now()->greaterThan($expiresAt)) {
            return response()->json(['code' => 'TOKEN_EXPIRED', 'message' => 'Platnosť aktivačného odkazu vypršala.'], 422);
        }

        if (!is_null($rec->consumed_at)) {
            return response()->json(['code' => 'ALREADY_ACTIVATED', 'message' => 'Účet už bol aktivovaný.'], 422);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['message' => 'Používateľ s danou e-mailovou adresou neexistuje.'], 422);
        }

        $temporaryPassword = Str::password(14);

        DB::transaction(function () use ($user, $temporaryPassword, $rec) {
            // 1) Activate the user and set temp password
            $user->password_hash = Hash::make($temporaryPassword);
            $user->active = true;
            $user->password_reset_needed = true;
            $user->save();

            // 2) Mark the activation token as consumed
            DB::table('company_activations')
                ->where('id', $rec->id)
                ->update(['consumed_at' => now()]);

            // 3) Flip the company owner profile to active + link the activation id
            //    (update all profiles for safety; schema suggests hasOne in practice)
            CompanyOwnerProfile::where('company_user_id', $user->id)
                ->update([
                    'is_active'             => true,
                    'company_activation_id' => $rec->id,
                    'updated_at'            => now(), // ensure timestamps are consistent
                ]);
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

    public function resend(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->input('email');
        $generic = ['message' => 'Ak účet existuje a nie je aktivovaný, poslali sme nový aktivačný email.'];

        /** @var User|null $user */
        $user = User::where('email', $email)->first();

        if (!$user || (bool) $user->active) {
            return response()->json($generic, 200);
        }

        $company = method_exists($user, 'companyOwnerProfile') && $user->companyOwnerProfile
            ? ($user->companyOwnerProfile->company ?? null)
            : null;

        DB::transaction(function () use ($user, $company) {
            // Remove any stale, unconsumed activations
            CompanyActivation::where('sent_to_mail', $user->email)
                ->whereNull('consumed_at')
                ->delete();

            // Create a fresh activation token
            $plainToken = Str::random(64);
            $activation = CompanyActivation::create([
                'hash'         => hash('sha256', $plainToken),
                'sent_to_mail' => $user->email,
                'created_at'   => now(),
                'consumed_at'  => null,
            ]);

            // Link the owner profile to the new activation record
            CompanyOwnerProfile::where('company_user_id', $user->id)
                ->update([
                    'company_activation_id' => $activation->id,
                    'updated_at'            => now(),
                ]);

            $baseUrl = rtrim(url('/'), '/');
            $activationUrl = $baseUrl . '/company/activate?' . http_build_query([
                    'token' => $plainToken,
                    'email' => $user->email,
                ]);

            $expiresText = Carbon::parse($activation->created_at)
                ->addHours(48)
                ->timezone('Europe/Bratislava')
                ->isoFormat('D.M.Y HH:mm');

            (new MailSender(
                'company_activation',
                [$user->email],
                [
                    'user'            => $user,
                    'company'         => $company,
                    'activationLink'  => $activationUrl,
                    'tokenExpiration' => $expiresText,
                ]
            ))->send();
        });

        return response()->json($generic, 200);
    }
}
