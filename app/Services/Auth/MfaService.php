<?php

namespace App\Services\Auth;

use App\Helpers\SessionHelper;
use App\Mail\CodeMfaMail;
use App\Models\User;
use App\Services\Crypto\Contracts\EncryptionServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;
use Random\RandomException;

class MfaService
{
    private const int CODE_LONGUEUR = 6;

    private const int CODE_EXPIRATION = 10;

    private const int MAX_TENTATIVES = 5;

    public function __construct(
        private readonly EncryptionServiceInterface $encryption,
        private readonly Google2FA $totp,
    ) {}

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

        Mail::to($user->email)->send(new CodeMfaMail($user, $code));
    }

    /**
     * @throws \SodiumException
     */
    public function verifierCode(User $user, string $codeSaisi): bool
    {
        $limite = 'mfa-verification:'.$user->id;
        if (RateLimiter::tooManyAttempts($limite, self::MAX_TENTATIVES)) {
            return false;
        }

        if (preg_match('/^[A-Z2-9]{4}-[A-Z2-9]{4}-[A-Z2-9]{4}$/i', $codeSaisi)) {
            $valide = $this->consumeRecoveryCode($user, strtoupper($codeSaisi));
            if ($valide) {
                RateLimiter::clear($limite);
            } else {
                RateLimiter::hit($limite, 600);
            }

            return $valide;
        }

        $type = SessionHelper::typeMfaPending() ?? 'email';
        $mfa = $user->mfa()->where('type', $type)->where('actif', true)->first();

        if (! $mfa) {
            return false;
        }

        if ($mfa->type === 'totp') {
            $valide = $this->verifierTotp($user, $codeSaisi);
            if (! $valide) {
                RateLimiter::hit($limite, 600);
            } else {
                RateLimiter::clear($limite);
            }

            return $valide;
        }

        if ($mfa->tentatives >= self::MAX_TENTATIVES) {
            return false;
        }

        if (! $mfa->code_expire_le || $mfa->code_expire_le->isPast()) {
            RateLimiter::hit($limite, 600);

            return false;
        }

        if (! Hash::check($codeSaisi, $mfa->code_hash)) {
            $mfa->increment('tentatives');
            RateLimiter::hit($limite, 600);

            return false;
        }

        $consumed = $user->mfa()->whereKey($mfa->id)
            ->where('code_hash', $mfa->code_hash)
            ->where('tentatives', '<', self::MAX_TENTATIVES)
            ->where('code_expire_le', '>', now())->update([
                'code_hash' => null,
                'code_expire_le' => null,
                'tentatives' => 0,
            ]);

        RateLimiter::clear($limite);

        return $consumed === 1;
    }

    /**
     * @throws RandomException
     */
    public function genererSecretTotp(User $user): array
    {
        $secret = $this->genererSecretBase32();

        $label = urlencode('Soldier:'.$user->email);
        $issuer = urlencode('Soldier');
        $otpauthUrl = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";

        return [
            'secret' => $secret,
            'otpauth_url' => $otpauthUrl,
        ];
    }

    /**
     * @throws \SodiumException
     */
    public function verifierTotp(User $user, string $code): bool
    {
        $mfa = $user->mfa()->where('type', 'totp')->where('actif', true)->first();

        if (! $mfa || ! $mfa->totp_secret_chiffre) {
            return false;
        }

        $kek = SessionHelper::obtenirKek();
        if (! $kek) {
            $clesPendantes = SessionHelper::obtenirMfaPendante();
            if (! $clesPendantes) {
                return false;
            }
            $kek = $clesPendantes['kek'];
        }

        $secretChiffre = json_decode($mfa->totp_secret_chiffre, true);
        $secret = $this->encryption->decrypt(
            $secretChiffre['ciphertext'],
            $kek,
            $secretChiffre['iv'],
            $secretChiffre['tag'],
        );
        sodium_memzero($kek);

        try {
            $step = $this->totp->verifyKeyNewer($secret, $code, $mfa->last_totp_step ?? 0, 1);
            if ($step === false) {
                return false;
            }

            return $user->mfa()->whereKey($mfa->id)
                ->where(fn ($q) => $q->whereNull('last_totp_step')->orWhere('last_totp_step', '<', $step))
                ->update(['last_totp_step' => $step]) === 1;
        } catch (IncompatibleWithGoogleAuthenticatorException) {
            return false;
        } catch (InvalidCharactersException) {
            return false;
        } catch (SecretKeyTooShortException) {
            return false;
        } finally {
            sodium_memzero($secret);
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

        $mfa = $user->mfa()->where('type', 'totp')->where('actif', true)->first();
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
        return str_pad((string) random_int(0, 999999), self::CODE_LONGUEUR, '0', STR_PAD_LEFT);
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
            for ($i = 0; $i < 4; $i++) {
                $segment .= $chars[random_int(0, strlen($chars) - 1)];
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
        return $this->totp->generateSecretKey(16);
    }

    private function consumeRecoveryCode(User $user, string $code): bool
    {
        $mfa = $user->mfa()->where('type', 'totp')->where('actif', true)->first();
        if (! $mfa) {
            return false;
        }
        $hashes = $mfa->codes_recuperation ?? [];
        foreach ($hashes as $index => $hash) {
            if (! Hash::check($code, $hash)) {
                continue;
            }
            unset($hashes[$index]);

            // Optimistic compare-and-swap: two requests cannot consume the
            // same recovery code, even when both read the original hash list.
            return $user->mfa()->whereKey($mfa->id)
                ->where('codes_recuperation', $mfa->getRawOriginal('codes_recuperation'))
                ->update(['codes_recuperation' => json_encode(array_values($hashes), JSON_THROW_ON_ERROR)]) === 1;
        }

        return false;
    }
}
