<?php

namespace App\Helpers;

use function Laravel\Prompts\search;

class SessionHelper
{
    private const string CLE_KEK = 'crypto.kek';
    private const string CLE_PRIVEE = 'crypto.cle_privee';
    private const string MFA_VERIFIE = 'auth.mfa_verifie';
    private const string MFA_USER_ID = 'auth.mfa_user_id_pending';

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

        return base64_decode($kekBase64);
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

    public static function haveClePrivee(): ?string
    {
        return session(self::CLE_PRIVEE);
    }

    public static function marquerMfaVerifie(): void
    {
        session([self::MFA_VERIFIE => true]);

    }

    public static function marquerMfaUserIdPending(): void
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
        ]);
    }
}
