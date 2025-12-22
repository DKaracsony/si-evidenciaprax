<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CompanyActivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\MailSender;
use App\Models\CompanyActivation;
use App\Models\CompanyOwnerProfile;

class CompanyActivationController extends Controller
{
    public function __construct(private CompanyActivationService $activationService) {}

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
            CompanyOwnerProfile::where('company_user_id', $user->id)
                ->update([
                    'is_active'             => true,
                    'company_activation_id' => $rec->id,
                    'updated_at'            => now(),
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

        /** @var User|null $user */
        $user = User::where('email', $email)->first();

        // 1) User neexistuje
        if (!$user) {
            return response()->json([
                'sent'    => false,
                'reason'  => 'NOT_FOUND',
                'message' => 'Účet s daným e-mailom neexistuje.',
            ], 200);
        }

        // 2) Už aktivovaný
        if ((bool) $user->active) {
            return response()->json([
                'sent'    => false,
                'reason'  => 'ALREADY_ACTIVATED',
                'message' => 'Účet už bol aktivovaný. Skúste sa prihlásiť.',
            ], 200);
        }

        // 3) Neaktívny účet → vytvor nový aktivačný link a pošli e-mail
        $company = method_exists($user, 'companyOwnerProfile') && $user->companyOwnerProfile
            ? ($user->companyOwnerProfile->company ?? null)
            : null;

        try {
            DB::transaction(function () use ($user, $company) {
                // Remove any stale, unconsumed activations
                CompanyActivation::where('sent_to_mail', $user->email)
                    ->whereNull('consumed_at')
                    ->delete();

                $this->activationService->createAndSendActivation($user, $company);
            });

            return response()->json([
                'sent'    => true,
                'reason'  => 'OK',
                'message' => 'Poslali sme vám nový aktivačný e-mail.',
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'sent'    => false,
                'reason'  => 'SEND_FAILED',
                'message' => 'Nepodarilo sa odoslať aktivačný e-mail. Skúste to prosím znova neskôr.',
            ], 500);
        }
    }
}
