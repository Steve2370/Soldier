<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Coffre\CleManagementService;
use App\Services\Coffre\CoffreService;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Crée l'identité et son coffre comme une seule unité de travail.
 *
 * Le contrôleur ne connaît ni la séquence cryptographique ni la structure
 * transactionnelle. Les clés en clair ne sortent de ce service qu'après un
 * commit réussi, afin d'être placées dans la session de l'utilisateur.
 */
readonly class UserRegistrationService
{
    public function __construct(
        private CleManagementService $cleManagement,
        private CoffreService $coffreService,
    ) {}

    /**
     * @return array{user: User, cles: array{kek: string, cle_privee: string}}
     * @throws Throwable
     * @throws \SodiumException
     */
    public function inscrire(array $donnees): array
    {
        return DB::transaction(function () use ($donnees): array {
            $cles = null;

            try {
                $user = User::create([
                    'name' => $donnees['name'],
                    'email' => $donnees['email'],
                    'password' => $donnees['password'],
                ]);

                $this->cleManagement->initialiserClesUser(
                    $user,
                    $donnees['master_password'],
                );

                $cles = $this->cleManagement->deverouillerCles(
                    $user,
                    $donnees['master_password'],
                );

                $this->coffreService->creerCoffre(
                    $user,
                    [
                        'nom' => 'Mon coffre',
                        'couleur' => '#217eaa',
                    ],
                    $cles['kek'],
                );

                return ['user' => $user, 'cles' => $cles];
            } catch (Throwable $exception) {
                if (is_array($cles)) {
                    sodium_memzero($cles['kek']);
                    sodium_memzero($cles['cle_privee']);
                }

                throw $exception;
            }
        });
    }
}
