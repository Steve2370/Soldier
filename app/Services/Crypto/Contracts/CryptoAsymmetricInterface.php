<?php

namespace App\Services\Crypto\Contracts;

interface CryptoAsymmetricInterface
{
    public function genererPaireCles(): array;
    public function chiffrerAvecClePublique(string $donnees, string $clePublique): string;
    public function decrypterAvecClePrivee(string $donneesCryptees, string $clePrivee): string;
}
