<?php

namespace App\Services\Crypto;

use App\Services\Crypto\Contracts\CleDerivationInterface;
use Random\RandomException;

class Argon2CleDerivation implements CleDerivationInterface
{
    private const int MEMOIRE_BYTES = 67_108_864;
    private const int ITERATIONS   = 4;
    private const int LONGUEUR_CLE = 32;
    private const int SALT_BYTES   = SODIUM_CRYPTO_PWHASH_SALTBYTES;

    /**
     * @throws RandomException
     * @throws \SodiumException
     */
    public function deriver(string $motDePasse, ?string $sel = null): array
    {
        $saltBinaire = $sel
            ? hex2bin($sel)
            : random_bytes(self::SALT_BYTES);

        $cle = $this->calculerCle($motDePasse, $saltBinaire);

        return [
            'cle' => $cle,
            'sel' => bin2hex($saltBinaire),
            'parametres' => $this->parametres(),
        ];
    }

    /**
     * @throws \SodiumException
     */
    public function recalculer(string $motDePasse, string $sel, array $parametres): string
    {
        return $this->calculerCle($motDePasse, hex2bin($sel));
    }

    /**
     * @throws \SodiumException
     */
    private function calculerCle(string $motDePasse, string $saltBinaire): string
    {
        return sodium_crypto_pwhash(
            self::LONGUEUR_CLE,
            $motDePasse,
            $saltBinaire,
            self::ITERATIONS,
            self::MEMOIRE_BYTES,
            SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
        );
    }

    private function parametres(): array
    {
        return [
            'algorithme' => 'argon2id',
            'version' => '1.3',
            'memoire' => self::MEMOIRE_BYTES,
            'iterations' => self::ITERATIONS,
            'longueur' => self::LONGUEUR_CLE,
        ];
    }
}
