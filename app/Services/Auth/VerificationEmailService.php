<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Auth\Contracts\VerificationEmailInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

final class VerificationEmailService implements VerificationEmailInterface
{
    public function send(User $user): bool
    {
        try {
            $user->sendEmailVerificationNotification();

            return true;
        } catch (Throwable $exception) {
            Log::warning('Email de vérification indisponible.', ['user_id' => $user->id, 'exception' => $exception::class]);

            return false;
        }
    }
}
