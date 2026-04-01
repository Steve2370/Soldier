<?php

namespace App\Services\Coffre;

use App\Exceptions\InvalidMasterPasswordException;
use App\Models\CleUser;
use App\Models\User;
use App\Services\Crypto\Contracts\CleDerivationInterface;
use App\Services\Crypto\Contracts\CryptoAsymmetricInterface;
use App\Services\Crypto\Contracts\EncryptionServiceInterface;
use RuntimeException;

readonly class CleManagementService
{
    public function __construct(
        private EncryptionServiceInterface $encryption,
        private CleDerivationInterface $cleDerivation,
        private CryptoAsymmetricInterface $asymmetric,
    ) {}

    /**
     * @throws \SodiumException
     */
    public function initialiserClesUser(User $user, string $masterPassword): CleUser
    {
        [
            'cle' => $masterCle,
            'sel' => $salt,
            'parametres' => $parametres,
        ] = $this->cleDerivation->deriver($masterPassword);

        \Log::info('initialiserClesUser', [
            'password_len' => strlen($masterPassword),
            'password_bytes' => bin2hex(substr($masterPassword, 0, 4)),
            'salt' => $salt,
            'masterCle_hex' => bin2hex(substr($masterCle, 0, 8)),
        ]);

        $kek = $this->encryption->genererCle(32);
        $kekChiffree = $this->encryption->encrypt($kek, $masterCle);

        [
            'cle_publique' => $clePublique,
            'cle_privee' => $clePrivee,
        ] = $this->asymmetric->genererPaireCles();

        $clePriveeChiffree = $this->encryption->encrypt($clePrivee, $kek);
        $verification = $this->creerVerification($masterCle);

        \Log::info('masterCle init check', [
            'strlen' => strlen($masterCle),
            'masterCle_hex' => bin2hex($masterCle),
        ]);

        sodium_memzero($masterCle);
        sodium_memzero($kek);
        sodium_memzero($clePrivee);

        return CleUser::create([
            'user_id' => $user->id,
            'kdf_salt' => $salt,
            'kdf_algorithme' => 'argon2id',
            'kdf_params' => $parametres,
            'encrypted_kek' => $kekChiffree,
            'public_key' => $clePublique,
            'encrypted_private_key' => $clePriveeChiffree,
            'verification_master_key' => $verification,
            'version_schema' => 1,
        ]);
    }

    /**
     * @throws \SodiumException
     */
    public function deverouillerCles(User $user, string $masterPassword): array
    {
        $cleUser = $user->clesUser;

        if (!$cleUser) {
            throw new RuntimeException("L'utilisateur {$user->id} n'a pas de clés initialisées.");
        }

        $masterCle = $this->cleDerivation->recalculer(
            $masterPassword,
            $cleUser->kdf_salt,
            $cleUser->kdf_params
        );

        \Log::info('deverouillerCles', [
            'password_len' => strlen($masterPassword),
            'password_bytes' => bin2hex(substr($masterPassword, 0, 4)),
            'salt_db' => $cleUser->kdf_salt,
            'masterCle_hex' => bin2hex(substr($masterCle, 0, 8)),
            'verification' => $cleUser->verification_master_key,
        ]);

        \Log::info('masterCle check', [
            'strlen' => strlen($masterCle),
            'is_binary' => !ctype_print($masterCle),
            'masterCle_hex' => bin2hex($masterCle),
        ]);

        if (!$this->verifierMasterKey($masterCle, $cleUser->verification_master_key)) {
            sodium_memzero($masterCle);
            throw new InvalidMasterPasswordException();
        }

        $kek = $this->encryption->decrypt(
            $cleUser->encrypted_kek['ciphertext'],
            $masterCle,
            $cleUser->encrypted_kek['iv'],
            $cleUser->encrypted_kek['tag'],
        );

        $clePrivee = $this->encryption->decrypt(
            $cleUser->encrypted_private_key['ciphertext'],
            $kek,
            $cleUser->encrypted_private_key['iv'],
            $cleUser->encrypted_private_key['tag'],
        );

        sodium_memzero($masterCle);

        return [
            'kek' => $kek,
            'cle_privee' => $clePrivee,
        ];
    }

    public function genererDataKeyCoffre(string $kek): array
    {
        $dataKey = $this->encryption->genererCle(32);
        $dataKeyChiffree = $this->encryption->encrypt($dataKey, $kek);

        return [
            'data_key' => $dataKey,
            'data_key_encrypted' => $dataKeyChiffree,
        ];
    }

    public function dechiffrerDataKeyCoffre(array $dataKeyChiffree, string $kek): string
    {
        return $this->encryption->decrypt(
            $dataKeyChiffree['ciphertext'],
            $kek,
            $dataKeyChiffree['iv'],
            $dataKeyChiffree['tag'],
        );
    }

    public function dechiffrerDataKeyCoffrePartage(
        string $dataKeyChiffreeDestinataire,
        string $clePrivee
    ): string {
        return $this->asymmetric->decrypterAvecClePrivee(
            $dataKeyChiffreeDestinataire,
            $clePrivee
        );
    }

    /**
     * @throws \Throwable
     * @throws \SodiumException
     */
    public function changerMasterPassword(
        User $user,
        string $ancienMotDePasse,
        string $nouveauMotDePasse,
    ): void {
        $cles = $this->deverouillerCles($user, $ancienMotDePasse);

        [
            'cle' => $nouvelleMasterCle,
            'sel' => $nouveauSalt,
            'parametres' => $nouveauxParametres,
        ] = $this->cleDerivation->deriver($nouveauMotDePasse);

        $nouvelleKekChiffree = $this->encryption->encrypt($cles['kek'], $nouvelleMasterCle);
        $nouvelleVerification = $this->creerVerification($nouvelleMasterCle);

        \DB::transaction(function () use (
            $user,
            $nouveauSalt,
            $nouveauxParametres,
            $nouvelleKekChiffree,
            $nouvelleVerification,
        ) {
            $user->clesUser->update([
                'kdf_salt' => $nouveauSalt,
                'kdf_params' => $nouveauxParametres,
                'encrypted_kek' => $nouvelleKekChiffree,
                'verification_master_key' => $nouvelleVerification,
            ]);
        });

        sodium_memzero($cles['kek']);
        sodium_memzero($nouvelleMasterCle);
    }

    private function creerVerification(string $masterCle): array
    {
        return $this->encryption->encrypt(
            'soldier-password-manager-verificateur-v1',
            $masterCle,
        );
    }

    private function verifierMasterKey(string $masterCle, array $verification): bool
    {
        try {
            \Log::info('verifierMasterKey', [
                'masterCle_hex' => bin2hex(substr($masterCle, 0, 8)),
                'ciphertext' => substr($verification['ciphertext'], 0, 20),
                'iv' => $verification['iv'],
                'tag' => $verification['tag'],
            ]);

            $texte = $this->encryption->decrypt(
                $verification['ciphertext'],
                $masterCle,
                $verification['iv'],
                $verification['tag'],
            );

            return hash_equals(
                'soldier-password-manager-verificateur-v1',
                $texte,
            );
        } catch (\Throwable $e) {
            \Log::error('verifierMasterKey decrypt failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'openssl' => openssl_error_string(),
            ]);
            return false;
        }
    }
}
