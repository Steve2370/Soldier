<?php

namespace App\Services\Notifications\Contracts;

use Illuminate\Mail\Mailable;

interface MailDeliveryInterface
{
    public function send(string $recipient, Mailable $message): bool;
}
