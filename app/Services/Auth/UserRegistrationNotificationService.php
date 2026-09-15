<?php

namespace App\Services\Auth;

use App\Mail\BienvenueMail;
use App\Models\User;
use App\Services\Auth\Contracts\UserRegistrationNotificationInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

readonly class UserRegistrationNotificationService implements UserRegistrationNotificationInterface
{
    /**
     * L'envoi d'email ne doit pas annuler une inscription déjà validée.
     * Le lien de vérification reste disponible via l'action de renvoi d'email.
     */
    public function envoyer(User $user): bool
    {
        try {
            $user->sendEmailVerificationNotification();
            Mail::to($user->email)->send(new BienvenueMail($user));

            return true;
        } catch (Throwable $exception) {
            Log::error('Échec de l’envoi des emails d’inscription.', [
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);

            return false;
        }
    }
}
