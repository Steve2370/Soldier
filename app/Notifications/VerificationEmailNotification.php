<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerificationEmailNotification extends VerifyEmail
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->mailer('resend')
            ->subject('Vérifiez votre adresse email — Soldier')
            ->view('emails.verification', ['url' => $url]);
    }
}
