<?php
namespace App\Services\Crypto;

use App\Exceptions\DecryptionException;
use App\Services\Crypto\Contracts\CryptoAsymmetricInterface;
use phpseclib4\Crypt\RSA;
use phpseclib4\Crypt\PublicKeyLoader;

class RsaCryptoService implements CryptoAsymmetricInterface
{
    private const int BITS_CLE = 4096;

    public function genererPaireCles(): array
    {
        $privateKey = RSA::createKey(self::BITS_CLE);
        $publicKey = $privateKey->getPublicKey();

        return [
            'cle_publique' => (string) $publicKey,
            'cle_privee' => (string) $privateKey,
        ];
    }

    public function chiffrerAvecClePublique(string $donnees, string $clePublique): string
    {
        $key = PublicKeyLoader::load($clePublique)
            ->withPadding(RSA::ENCRYPTION_OAEP)
            ->withHash('sha256')
            ->withMGFHash('sha256');

        $ciphertext = $key->encrypt($donnees);

        if ($ciphertext === false) {
            throw new \RuntimeException('Échec du chiffrement RSA.');
        }

        return base64_encode($ciphertext);
    }

    public function decrypterAvecClePrivee(string $donneesCryptees, string $clePrivee): string
    {
        $key = PublicKeyLoader::load($clePrivee)
            ->withPadding(RSA::ENCRYPTION_OAEP)
            ->withHash('sha256')
            ->withMGFHash('sha256');

        $ciphertext = base64_decode($donneesCryptees, true);
        if ($ciphertext === false || $ciphertext === '') {
            throw new DecryptionException();
        }

        $plaintext = $key->decrypt($ciphertext);

        if ($plaintext === false) {
            throw new DecryptionException();
        }

        return $plaintext;
    }
}
