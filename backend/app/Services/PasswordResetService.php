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
    public function issueTokenAndSendMail(string $rawEmail): void
    {
        $inputEmail = mb_strtolower(trim($rawEmail));
        $user = User::whereRaw('LOWER(email) = ?', [$inputEmail])->first();

        // 1) only active users receive resets
        if (!$user || !($user->active ?? false)) {
            return;
        }

        // 2) Token generation + rotation
        $plainToken = Str::random(64);
        $hash       = Hash::make($plainToken);
        $expiresAt  = Carbon::now()->addHours(2);

        DB::transaction(function () use ($user, $hash, $expiresAt) {
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
                'sent_to_mail' => $user->email,
            ]);
        });

        // 3) Reset URL + e-mail
        $resetUrl = $this->makeResetUrl($plainToken, $user->email);

        (new MailSender(
            'password_reset',
            [$user->email],
            ['email' => $user->email, 'reset_url' => $resetUrl]
        ))->send();
    }

    private function makeResetUrl(string $plainToken, string $email): string
    {
        $base = rtrim(url('/'), '/');
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
