<?php

namespace App\Helpers;

class SessionHelper
{
    private const string CLE_KEK = 'crypto.kek';
    private const string CLE_PRIVEE = 'crypto.cle_privee';
    private const string MFA_VERIFIE = 'auth.mfa_verifie';
    private const string MFA_USER_ID = 'auth.mfa_user_id_pending';
    private const string MFA_PENDING_KEK = 'auth.mfa_pending_kek';
    private const string MFA_PENDING_PRIVATE_KEY = 'auth.mfa_pending_private_key';
    private const string MFA_TYPE = 'auth.mfa_type';

    public static function stockerKek(string $cleKek): void
    {
        session([self::CLE_KEK => base64_encode($cleKek)]);
    }

    public static function obtenirKek(): ?string
    {
        $kekBase64 = session(self::CLE_KEK);

        if (!$kekBase64) {
            return null;
        }

        $kek = base64_decode($kekBase64, true);

        return $kek !== false && strlen($kek) === 32 ? $kek : null;
    }

    public static function havecleKek(): bool
    {
        return session()->has(self::CLE_KEK);
    }

    public static function stockerClePrivee(string $clePrivee): void
    {
        session([self::CLE_PRIVEE => $clePrivee]);
    }

    public static function obtenirClePrivee(): ?string
    {
        return session(self::CLE_PRIVEE);

    }

    public static function haveClePrivee(): bool
    {
        return session()->has(self::CLE_PRIVEE);
    }

    public static function marquerMfaVerifie(): void
    {
        session([self::MFA_VERIFIE => true]);

    }

    public static function mfaVerifie(): bool
    {
        return session(self::MFA_VERIFIE, false) === true;
    }

    public static function mfaUserIdPending(int $userId): void
    {
        session([self::MFA_USER_ID => $userId]);
    }

    public static function stockerMfaPendante(string $kek, string $clePrivee, string $type): void
    {
        session([
            self::MFA_PENDING_KEK => base64_encode($kek),
            self::MFA_PENDING_PRIVATE_KEY => $clePrivee,
            self::MFA_TYPE => $type,
        ]);
    }

    /** @return array{kek: string, cle_privee: string}|null */
    public static function obtenirMfaPendante(): ?array
    {
        $kek = base64_decode((string) session(self::MFA_PENDING_KEK), true);
        $clePrivee = session(self::MFA_PENDING_PRIVATE_KEY);

        if ($kek === false || strlen($kek) !== 32 || !is_string($clePrivee) || $clePrivee === '') {
            return null;
        }

        return ['kek' => $kek, 'cle_privee' => $clePrivee];
    }

    public static function typeMfaPending(): ?string
    {
        $type = session(self::MFA_TYPE);

        return is_string($type) ? $type : null;
    }

    public static function effacerMfaPendante(): void
    {
        session()->forget([
            self::MFA_PENDING_KEK,
            self::MFA_PENDING_PRIVATE_KEY,
            self::MFA_TYPE,
            self::MFA_USER_ID,
        ]);
    }

    public static function obtenirMfaUserIdPending(): ?int
    {
        return session(self::MFA_USER_ID);
    }

    public static function deverouiller(string $cleKek, string $clePrivee): void
    {
        self::stockerKek($cleKek);
        self::stockerClePrivee($clePrivee);
    }

    public static function effacerCles(): void
    {
        session()->forget([
            self::CLE_KEK,
            self::CLE_PRIVEE,
            self::MFA_VERIFIE,
            self::MFA_USER_ID,
            self::MFA_PENDING_KEK,
            self::MFA_PENDING_PRIVATE_KEY,
            self::MFA_TYPE,
        ]);
    }
}
