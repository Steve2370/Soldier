<?php

namespace App\Services\Auth;

use App\Mail\BienvenueMail;
use App\Models\User;
use App\Services\Auth\Contracts\UserRegistrationNotificationInterface;
use App\Services\Auth\Contracts\VerificationEmailInterface;
use App\Services\Notifications\Contracts\MailDeliveryInterface;

readonly class UserRegistrationNotificationService implements UserRegistrationNotificationInterface
{
    public function __construct(
        private VerificationEmailInterface $verification,
        private MailDeliveryInterface $mail,
    ) {}

    /**
     * L'envoi d'email ne doit pas annuler une inscription déjà validée.
     * Le lien de vérification reste disponible via l'action de renvoi d'email.
     */
    public function envoyer(User $user): bool
    {
        $sent = $this->verification->send($user);
        $this->mail->send($user->email, new BienvenueMail($user));

        return $sent;
    }
}
