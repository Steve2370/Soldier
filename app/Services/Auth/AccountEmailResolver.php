<?php

namespace App\Services\Auth;

use App\Models\User;

final class AccountEmailResolver
{
    public function find(string $email): ?User
    {
        // Also supports historical mixed-case addresses. Ambiguous legacy
        // duplicates fail closed instead of selecting an arbitrary account.
        $users = User::whereRaw('LOWER(email) = ?', [mb_strtolower(trim($email))])->limit(2)->get();

        return $users->count() === 1 ? $users->first() : null;
    }
}
