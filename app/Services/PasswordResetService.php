<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\MailSender;

class PasswordResetService
{
    public function issueTokenAndSendMail(string $rawEmail, ?string $ip = null, ?string $ua = null): void
    {
        $inputEmail = mb_strtolower(trim($rawEmail));

        // 1) Decision: which user and which address should the email go to?
        [$user, $deliverTo] = $this->resolveUserAndRecipientEmail($inputEmail);

        // anti-enumeration + only active users receive resets
        if (!$user || !($user->active ?? false) || $deliverTo === '') {
            return;
        }

        // 2) Token generation + rotation
        $plainToken = Str::random(64);
        $hash       = Hash::make($plainToken);
        $expiresAt  = Carbon::now()->addHours(2);

        DB::transaction(function () use ($user, $hash, $expiresAt, $deliverTo) {
            DB::table('password_resets')
                ->where('user_id', $user->id)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            DB::table('password_resets')->insert([
                'user_id'      => $user->id,
                'hash'         => $hash,
                'expires_at'   => $expiresAt,
                'created_at'   => now(),
                'consumed_at'  => null,
                'sent_to_mail' => $deliverTo,
            ]);
        });

        // 3) Reset URL + e-mail
        $resetUrl = $this->makeResetUrl($plainToken, $deliverTo);

        (new MailSender(
            'password_reset',
            [$deliverTo],
            ['email' => $deliverTo, 'reset_url' => $resetUrl]
        ))->send();
    }

    private function resolveUserAndRecipientEmail(string $inputEmail): array
    {
        // 1 Student: student_profiles.student_email
        $studentProfile = DB::table('student_profiles')
            ->whereRaw('LOWER(student_email) = ?', [$inputEmail])
            ->first();

        if ($studentProfile && $studentProfile->student_user_id) {
            $user = User::find($studentProfile->student_user_id);

            if ($user) {
                $deliverTo = mb_strtolower((string) $studentProfile->student_email);

                // optional domain check
                if ($deliverTo !== '' && $this->domainOf($deliverTo) === 'student.ukf.sk') {
                    return [$user, $deliverTo];
                }
            }
        }

        // 2 Not student / fallback: users.email
        $user = User::whereRaw('LOWER(email) = ?', [$inputEmail])->first();

        if ($user && !empty($user->email)) {
            return [$user, mb_strtolower($user->email)];
        }

        return [null, ''];
    }

    private function domainOf(string $email): string
    {
        $at = strrchr($email, '@');
        return $at ? mb_strtolower(ltrim($at, '@')) : '';
    }

    private function makeResetUrl(string $plainToken, string $email): string
    {
        // Backend APP_URL
        $base = rtrim(
            (string) (config('app.url') ?? env('APP_URL') ?? 'http://127.0.0.1:8000'),
            '/'
        );

        $query = http_build_query([
            'token' => $plainToken,
            'email' => $email,
        ]);

        return $base . '/password/reset?' . $query;
    }


    public function resetPassword(string $rawEmail, string $plainToken, string $newPassword): void
    {
        $email = mb_strtolower(trim($rawEmail));

        // 1) Existing, unused token
        $record = DB::table('password_resets')
            ->where('sent_to_mail', $email)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if (!$record) {
            return;
        }

        // 2) Comparison of plain token with bcrypt hash
        if (!Hash::check($plainToken, $record->hash)) {
            return;
        }

        // 3) User query
        $user = User::find($record->user_id);
        if (!$user || !($user->active ?? false)) {
            return;
        }

        // 4) Password update + token consumption
        DB::transaction(function () use ($user, $newPassword, $record) {
            $user->password_hash = Hash::make($newPassword);
            $user->save();

            // invalidate token
            DB::table('password_resets')
                ->where('id', $record->id)
                ->update(['consumed_at' => now()]);
        });
    }
}
