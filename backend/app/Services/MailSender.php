<?php

namespace App\Services;

use App\Mail\GenericTemplateMail;
use InvalidArgumentException;
use Illuminate\Support\Facades\Mail;

class MailSender{
    protected string $templateKey;
    protected array $variables;
    protected array $sendTo;
    protected string $replyTo = 'evidencia.praxe@gmail.com';

    public function __construct(string $templateKey,  array $sendTo, array $variables = [])
    {
        $this->templateKey = $templateKey;
        $this->sendTo      = array_values($sendTo);
        $this->variables   = $variables;
    }

    public function send(): void
    {
        $config = config("mail_templates.{$this->templateKey}");

        if (!is_array($config) || empty($config['view']) || empty($config['subject'])) {
            throw new InvalidArgumentException("Invalid mail template key: {$this->templateKey}");
        }

        Mail::to($this->sendTo)->queue(
            new GenericTemplateMail(
                $config['view'],
                $config['subject'],
                $this->variables,
                $this->replyTo
            )
        );
    }
}
