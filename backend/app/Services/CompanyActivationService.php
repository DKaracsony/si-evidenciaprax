<?php

namespace App\Services;

use App\Models\CompanyActivation;
use App\Models\CompanyOwnerProfile;
use App\Services\MailSender;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CompanyActivationService
{
    public function createAndSendActivation($user, $company): void
    {
        $plainToken = Str::random(64);

        $activation = CompanyActivation::create([
            'hash'         => hash('sha256', $plainToken),
            'sent_to_mail' => $user->email,
            'created_at'   => now(),
            'consumed_at'  => null,
        ]);

        CompanyOwnerProfile::where('company_user_id', $user->id)
            ->update([
                'company_activation_id' => $activation->id,
                'updated_at'            => now(),
            ]);

        $activationUrl = $this->buildActivationUrl($plainToken, $user->email);
        $expiresText   = $this->getExpirationText($activation->created_at);

        $this->sendActivationEmail($user, $company, $activationUrl, $expiresText);
    }

    private function buildActivationUrl(string $token, string $email): string
    {
        $baseUrl = config('frontend.url');

        return $baseUrl . '/company/activate?' . http_build_query([
                'token' => $token,
                'email' => $email,
            ]);
    }

    private function getExpirationText(string $createdAt): string
    {
        return Carbon::parse($createdAt)
            ->addHours(48)
            ->timezone('Europe/Bratislava')
            ->isoFormat('D.M.Y HH:mm');
    }

    private function sendActivationEmail($user, $company, string $activationUrl, string $expiresText): void
    {
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
    }
}
