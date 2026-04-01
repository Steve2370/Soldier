<?php

namespace App\Services\Crypto;

use App\Services\Crypto\Contracts\EncryptionServiceInterface;
use App\Exceptions\DecryptionException;
use Random\RandomException;
use RuntimeException;

class AesEncryptionService implements EncryptionServiceInterface
{
    private const string CIPHER    = 'aes-256-gcm';
    private const int IV_BYTES  = 12;
    private const int TAG_BYTES = 16;
    private const int CLE_BYTES = 32;

    /**
     * @throws RandomException
     */
    public function encrypt(string $data, string $key): array
    {
        $this->validerCle($key);

        $iv  = random_bytes(self::IV_BYTES);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $data,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_BYTES
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Échec du chiffrement AES-GCM : ' . openssl_error_string());
        }

        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
        ];
    }

    public function decrypt(
        string $ciphertext,
        string $key,
        string $iv,
        string $tag
    ): string {
        $this->validerCle($key);

        $plaintext = openssl_decrypt(
            base64_decode($ciphertext),
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            base64_decode($iv),
            base64_decode($tag)
        );

        if ($plaintext === false) {
            throw new DecryptionException();
        }

        return $plaintext;
    }

    /**
     * @throws RandomException
     */
    public function genererCle(int $bytes = 32): string
    {
        return random_bytes($bytes);
    }

    private function validerCle(string $cle): void
    {
        if (strlen($cle) !== self::CLE_BYTES) {
            throw new \InvalidArgumentException(
                sprintf(
                    'La clé AES-256 doit faire %d bytes, %d bytes fournis.',
                    self::CLE_BYTES,
                    strlen($cle)
                )
            );
        }
    }
}
