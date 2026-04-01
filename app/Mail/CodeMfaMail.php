<?php

namespace App\Mail;

use App\Models\User;

class CodeMfaMail
{

    /**
     * @param User $user
     * @param string $code
     */
    public function __construct(User $user, string $code)
    {
    }
}
