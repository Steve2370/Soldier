<?php

namespace App\Services\Crypto;

use App\Exceptions\DecryptionException;
use App\Services\Crypto\Contracts\CryptoAsymmetricInterface;
use Illuminate\Contracts\Encryption\DecryptException;

class RsaCryptoService implements CryptoAsymmetricInterface
{
    private const int BITS_CLE = 4096;
    private const int PADDING = OPENSSL_PKCS1_OAEP_PADDING;

    public function genererPaireCles(): array
    {
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => self::BITS_CLE,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $ressouces = openssl_pkey_new($config);

        if ($ressouces === false) {
            throw new \RuntimeException('Impossible de générer la paire de clés RSA : ' . openssl_error_string());
        }

        openssl_pkey_export($ressouces, $privateKey);
        $details = openssl_pkey_get_details($ressouces);
        $clePublique = $details['key'];

        return [
            'cle_publique' => $clePublique,
            'cle_privee' => $privateKey,
        ];
    }

    public function chiffrerAvecClePublique(string $donnees, string $clePublique): string
    {
        $ressouces = openssl_pkey_get_public($clePublique);

        if ($ressouces === false) {
            throw new \InvalidArgumentException('Clé publique RSA invalide : ' . openssl_error_string()
            );
        }

        $resultat = openssl_public_encrypt($donnees, $ciphertext, $ressouces, self::PADDING);

        if ($resultat === false) {
            throw new \RuntimeException('Échec du chiffrement RSA : ' . openssl_error_string());
        }

        return base64_encode($ciphertext);
    }

    public function decrypterAvecClePrivee(string $donneesCryptees, string $clePrivee): string
    {
        $ressouces = openssl_pkey_get_private($clePrivee);

        if ($ressouces === false) {
            throw new \InvalidArgumentException('Clé privée RSA invalide : ' . openssl_error_string());
        }

        $resultat = openssl_private_decrypt(
            base64_decode($donneesCryptees), $plaintext, $ressouces, self::PADDING);

        if ($resultat === false) {
            throw new DecryptionException();
        }

        return $plaintext;
    }
}
