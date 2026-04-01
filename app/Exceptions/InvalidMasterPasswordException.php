<?php

namespace App\Exceptions;

class InvalidMasterPasswordException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Le master password est incorrect.');
    }
}
