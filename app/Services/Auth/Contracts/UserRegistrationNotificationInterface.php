<?php

namespace App\Services\Auth\Contracts;

use App\Models\User;

interface UserRegistrationNotificationInterface
{
    public function envoyer(User $user): bool;
}
