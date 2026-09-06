<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Échange OAuth -> extension Chrome : au lieu de transmettre un jeton Sanctum
 * (credential longue durée) directement dans l'URL de redirection — visible dans
 * l'historique du navigateur, les logs serveur/proxy, et un éventuel Referer —,
 * on transmet un code opaque à usage unique et très courte durée de vie. Le jeton
 * réel n'est créé qu'au moment de l'échange, côté serveur, jamais dans une URL.
 */
class ExtensionHandoffService
{
    private const int DUREE_VIE_SECONDES = 60;
    private const string PREFIXE_CACHE = 'oauth_extension_code:';

    public function genererCode(User $user): string
    {
        $code = Str::random(64);

        Cache::put(self::PREFIXE_CACHE . $code, $user->id, self::DUREE_VIE_SECONDES);

        return $code;
    }

    /**
     * Consomme le code (usage unique) et retourne l'utilisateur associé, ou null
     * si le code est invalide, déjà utilisé, ou expiré.
     */
    public function resoudreCode(string $code): ?User
    {
        $userId = Cache::pull(self::PREFIXE_CACHE . $code);

        if (!$userId) {
            return null;
        }

        return User::find($userId);
    }
}
