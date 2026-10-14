<?php

namespace App\Services\Auth;

use App\Helpers\SessionHelper;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class MfaChallengeService
{
    public function __construct(private MfaService $mfa) {}

    public function begin(User $user, string $kek, string $privateKey): bool
    {
        $type = $user->mfa()->where('type', 'email')->where('actif', true)->exists() ? 'email' : 'totp';
        // A transport failure must never leave a fully authenticated session.
        Auth::logout();
        SessionHelper::effacerCles();
        session()->regenerate(true);
        SessionHelper::stockerMfaPendante($kek, $privateKey, $type);
        SessionHelper::mfaUserIdPending($user->id);

        try {
            if ($type === 'email') {
                $this->mfa->envoyerCodeEmail($user);
            }

            return true;
        } catch (Throwable $exception) {
            SessionHelper::effacerCles();
            Log::warning('Challenge MFA indisponible.', ['user_id' => $user->id, 'exception' => $exception::class]);

            return false;
        }
    }
}
