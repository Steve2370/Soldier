<?php

namespace App\Services\Crypto\Contracts;

interface EncryptionServiceInterface
{
    public function encrypt(string $plaintext, string $key): array;
    public function decrypt(
        string $ciphertext,
        string $key,
        string $iv,
        string $tag
    ): string;

    public function genererCle(int $bytes = 32): string;
}
