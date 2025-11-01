<?php

namespace App\Mail;

use App\Models\CompanyActivation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CompanyActivation $activation,
        public string $plainToken
    ) {}

    public function build()
    {
        $activationLink = url("/activate?token=" . urlencode($this->plainToken));
        $tokenExpiration = $this->activation->expires_at->format('d.m.Y H:i');

        return $this
            ->subject('Aktivujte svoj firemný účet')
            ->markdown('emails.company_activation', [
                'activation' => $this->activation,
                'activationLink' => $activationLink,
                'tokenExpiration' => $tokenExpiration,
            ]);
    }
}
