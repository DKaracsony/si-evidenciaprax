<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Company $company,
        public string $activationLink,
        public string $tokenExpiration
    ) {}

    public function build()
    {
        return $this
            ->subject('Aktivujte svoj firemný účet')
            ->markdown('emails.company_activation', [
                'user'             => $this->user,
                'company'          => $this->company,
                'activationLink'   => $this->activationLink,
                'tokenExpiration'  => $this->tokenExpiration,
            ]);
    }
}
