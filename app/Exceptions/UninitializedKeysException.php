<?php

namespace App\Exceptions;

/**
 * Levée quand un compte existe (ligne `users`) mais n'a aucune ligne `cles_user`
 * associée — typiquement un enregistrement interrompu avant l'ajout des correctifs
 * de septembre 2026 (echec de generation de cles RSA laissant un compte orphelin,
 * cf. RsaCryptoService/phpseclib). Architecture zero-knowledge : sans cette ligne,
 * il n'existe aucun moyen de recuperer ou reconstituer les cles - le compte doit
 * etre recree.
 */
class UninitializedKeysException extends \RuntimeException
{
    public function __construct(int $userId)
    {
        parent::__construct("L'utilisateur {$userId} n'a pas de clés initialisées.");
    }
}
