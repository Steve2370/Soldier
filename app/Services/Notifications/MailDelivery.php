<?php

namespace App\Services\Notifications;

use App\Services\Notifications\Contracts\MailDeliveryInterface;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class MailDelivery implements MailDeliveryInterface
{
    public function send(string $recipient, Mailable $message): bool
    {
        try {
            Mail::to($recipient)->send($message);

            return true;
        } catch (Throwable $exception) {
            Log::warning('Notification email indisponible.', ['message_type' => $message::class, 'exception' => $exception::class]);

            return false;
        }
    }
}
