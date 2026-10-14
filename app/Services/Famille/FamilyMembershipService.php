<?php

namespace App\Services\Famille;

use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\ShareCoffre;
use App\Models\User;
use App\Services\Coffre\CleManagementService;
use App\Services\Crypto\Contracts\CryptoAsymmetricInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class FamilyMembershipService
{
    public function __construct(private CleManagementService $keys, private CryptoAsymmetricInterface $asymmetric) {}

    public function add(FamilyGroup $group, User $recipient, string $kek): FamilyMember
    {
        return DB::transaction(function () use ($group, $recipient, $kek): FamilyMember {
            $group = FamilyGroup::query()->lockForUpdate()->findOrFail($group->id);
            if ($group->isFull() || $group->members()->where('user_id', $recipient->id)->exists()) {
                throw ValidationException::withMessages(['email' => 'Groupe complet ou utilisateur déjà membre.']);
            }
            $publicKey = $recipient->clesUser?->public_key;
            if (! $publicKey || ! $group->coffre_id) {
                throw ValidationException::withMessages(['email' => 'Le destinataire doit d’abord initialiser son coffre.']);
            }
            $vault = $group->owner->coffres()->findOrFail($group->coffre_id);
            $dataKey = $this->keys->dechiffrerDataKeyCoffre($vault->data_key_encrypted, $kek);
            try {
                $envelope = $this->asymmetric->chiffrerAvecClePublique($dataKey, $publicKey);
            } finally {
                sodium_memzero($dataKey);
            }
            ShareCoffre::updateOrCreate(['coffre_id' => $vault->id, 'destinataire_id' => $recipient->id], [
                'proprietaire_id' => $group->owner_id, 'family_group_id' => $group->id,
                'data_key_destinataire_encrypted' => $envelope, 'permission' => 'ecriture',
                'statut' => 'accepte', 'accepte_le' => now(), 'expire_le' => null, 'element_ids' => null,
            ]);

            return $group->members()->create(['user_id' => $recipient->id, 'role' => 'member', 'joined_at' => now()]);
        });
    }
}
