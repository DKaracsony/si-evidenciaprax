<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericTemplateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $viewName,
        public string $subjectLine,
        public array $variables,
        public ?string $replyToAddress = null
    ) {}

    public function build()
    {
        $mail = $this->subject($this->subjectLine)->view($this->viewName, $this->variables);

        if ($this->replyToAddress)
            $mail->replyTo($this->replyToAddress);

        return $mail;
    }
}
