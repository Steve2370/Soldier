<?php

namespace App\Services\Auth\Contracts;

use App\Models\User;

interface VerificationEmailInterface
{
    public function send(User $user): bool;
}
