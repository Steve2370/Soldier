<?php

namespace App\Services\Auth;

use App\Helpers\SessionHelper;
use App\Mail\CodeMfaMail;
use App\Models\User;
use App\Services\Crypto\Contracts\EncryptionServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;
use Random\RandomException;

class MfaService
{
    private const int CODE_LONGUEUR  = 6;
    private const int CODE_EXPIRATION = 10;
    private const int MAX_TENTATIVES = 5;

    /**
     * @throws RandomException
     */
    public function envoyerCodeEmail(User $user): void
    {
        $code = $this->genererCode();

        $mfa = $user->mfa()->firstOrCreate(
            ['type' => 'email'],
            ['actif' => true]
        );

        $mfa->update([
            'code_hash' => Hash::make($code),
            'code_expire_le' => now()->addMinutes(self::CODE_EXPIRATION),
            'tentatives' => 0,
            'actif' => true,
        ]);

//        Mail::to($user->email)->send(new CodeMfaMail($user, $code));
    }

    /**
     * @throws \SodiumException
     */
    public function verifierCode(User $user, string $codeSaisi): bool
    {
        $mfa = $user->mfa()->where('actif', true)->first();

        if (!$mfa) {
            return false;
        }

        if ($mfa->type === 'totp') {
            return $this->verifierTotp($user, $codeSaisi);
        }

        if ($mfa->tentatives >= self::MAX_TENTATIVES) {
            return false;
        }

        if ($mfa->code_expire_le < now()) {
            return false;
        }

        if (!Hash::check($codeSaisi, $mfa->code_hash)) {
            $mfa->increment('tentatives');
            return false;
        }

        $mfa->update([
            'code_hash' => null,
            'code_expire_le' => null,
            'tentatives' => 0,
        ]);

        return true;
    }

    /**
     * @throws RandomException
     */
    public function genererSecretTotp(User $user): array
    {
        $secret = $this->genererSecretBase32();

        $label  = urlencode('Soldier:' . $user->email);
        $issuer = urlencode('Soldier');
        $otpauthUrl = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";

        return [
            'secret' => $secret,
            'otpauth_url' => $otpauthUrl,
            'qr_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpauthUrl),
        ];
    }

    /**
     * @throws \SodiumException
     */
    public function verifierTotp(User $user, string $code): bool
    {
        $mfa = $user->mfa()->where('type', 'totp')->where('actif', true)->first();

        if (!$mfa || !$mfa->totp_secret_chiffre) {
            return false;
        }

        $kek = SessionHelper::obtenirKek();
        if (!$kek) {
            $kekPending = session('mfa_pending_kek');
            if (!$kekPending) return false;
            $kek = base64_decode($kekPending);
        }

        $secretChiffre = json_decode($mfa->totp_secret_chiffre, true);
        $secret = app(EncryptionServiceInterface::class)
            ->decrypt(
                $secretChiffre['ciphertext'],
                $kek,
                $secretChiffre['iv'],
                $secretChiffre['tag'],
            );
        sodium_memzero($kek);

        $google2fa = new Google2FA();
        \Log::info('totp verify', [
            'secret_len' => strlen($secret),
            'secret' => $secret,
            'code' => $code,
        ]);
        try {
            return $google2fa->verifyKey($secret, $code, 1);
        } catch (IncompatibleWithGoogleAuthenticatorException $e) {
            return false;
        } catch (InvalidCharactersException $e) {
            return false;
        } catch (SecretKeyTooShortException $e) {
            return false;
        }
    }

    /**
     * @throws RandomException
     */
    public function genererCodesRecuperation(User $user): array
    {
        $codes = [];
        $codesHashes = [];

        for ($i = 0; $i < 8; $i++) {
            $code = $this->genererCodeRecuperation();
            $codes[] = $code;
            $codesHashes[] = Hash::make($code);
        }

        $mfa = $user->mfa()->where('actif', true)->first();
        if ($mfa) {
            $mfa->update(['codes_recuperation' => $codesHashes]);
        }

        return $codes;
    }

    /**
     * @throws RandomException
     */
    private function genererCode(): string
    {
        $bytes = random_bytes(4);
        $int = hexdec(bin2hex($bytes));
        return str_pad($int % pow(10, self::CODE_LONGUEUR), self::CODE_LONGUEUR, '0', STR_PAD_LEFT);
    }

    /**
     * @throws RandomException
     */
    private function genererCodeRecuperation(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $segments = [];
        for ($s = 0; $s < 3; $s++) {
            $segment = '';
            $bytes = random_bytes(4);
            for ($i = 0; $i < 4; $i++) {
                $segment .= $chars[ord($bytes[$i]) % strlen($chars)];
            }
            $segments[] = $segment;
        }
        return implode('-', $segments);
    }

    /**
     * @throws RandomException
     */
    private function genererSecretBase32(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey(16);
    }
}
