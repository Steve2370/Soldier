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
        $saltBinaire = $sel ? $this->decoderSel($sel) : random_bytes(self::SALT_BYTES);

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
        return $this->calculerCle($motDePasse, $this->decoderSel($sel), $parametres);
    }

    /**
     * @throws \SodiumException
     */
    private function calculerCle(string $motDePasse, string $saltBinaire, ?array $parametres = null): string
    {
        $parametres ??= $this->parametres();
        $this->validerParametres($parametres);

        return sodium_crypto_pwhash(
            $parametres['longueur'],
            $motDePasse,
            $saltBinaire,
            $parametres['iterations'],
            $parametres['memoire'],
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

    private function decoderSel(string $sel): string
    {
        $sel = trim($sel);

        if (!ctype_xdigit($sel) || strlen($sel) !== self::SALT_BYTES * 2) {
            throw new \InvalidArgumentException('Sel Argon2id invalide.');
        }

        $decode = hex2bin($sel);

        if ($decode === false) {
            throw new \InvalidArgumentException('Sel Argon2id invalide.');
        }

        return $decode;
    }

    private function validerParametres(array $parametres): void
    {
        $attendus = ['algorithme', 'version', 'memoire', 'iterations', 'longueur'];
        if (array_diff($attendus, array_keys($parametres)) !== []) {
            throw new \InvalidArgumentException('Paramètres Argon2id incomplets.');
        }

        if ($parametres['algorithme'] !== 'argon2id'
            || $parametres['version'] !== '1.3'
            || (int) $parametres['longueur'] !== self::LONGUEUR_CLE
            || (int) $parametres['memoire'] < SODIUM_CRYPTO_PWHASH_MEMLIMIT_MIN
            || (int) $parametres['memoire'] > 268_435_456
            || (int) $parametres['iterations'] < SODIUM_CRYPTO_PWHASH_OPSLIMIT_MIN
            || (int) $parametres['iterations'] > 10
        ) {
            throw new \InvalidArgumentException('Paramètres Argon2id non autorisés.');
        }
    }
}
