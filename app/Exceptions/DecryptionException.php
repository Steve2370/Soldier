<?php

namespace App\Exceptions;

class DecryptionException extends \RuntimeException
{
    public function __construct(string $message = "Déchiffrement impossible.")
    {
        parent::__construct($message);
    }
}
