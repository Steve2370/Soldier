<?php

namespace App\Services\Partage;

use App\Models\FamilyGroup;
use App\Models\InvitationPartage;
use App\Models\ShareCoffre;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AcceptShareInvitation
{
    public function accept(User $recipient, string $token): ShareCoffre
    {
        return DB::transaction(function () use ($recipient, $token): ShareCoffre {
            $hash = hash('sha256', $token);
            $preview = InvitationPartage::where('token_hash', $hash)->firstOrFail();
            // Match the lock order used by family removal: group, then invitation.
            $group = $preview->family_group_id
                ? FamilyGroup::query()->lockForUpdate()->findOrFail($preview->family_group_id)
                : FamilyGroup::where('coffre_id', $preview->coffre_id)->lockForUpdate()->first();
            if ($group) {
                abort_unless($group->members()->where('user_id', $recipient->id)->exists(), 403);
            }
            $invitation = InvitationPartage::whereKey($preview->id)->where('statut', 'en_attente')
                ->where('expire_le', '>', now())->lockForUpdate()->firstOrFail();
            abort_unless(mb_strtolower($invitation->email_destinataire) === mb_strtolower($recipient->email), 403);
            abort_unless($recipient->hasVerifiedEmail(), 403);
            if (! $invitation->data_key_encrypted || ! $invitation->coffre || ! $invitation->expediteur) {
                throw ValidationException::withMessages(['invitation' => 'Ce partage doit être renvoyé par son propriétaire.']);
            }

            // Serializes different invitations for the same vault as well.
            $invitation->coffre()->lockForUpdate()->firstOrFail();
            $share = ShareCoffre::firstOrCreate([
                'coffre_id' => $invitation->coffre_id, 'destinataire_id' => $recipient->id,
            ], [
                'proprietaire_id' => $invitation->expediteur_id,
                'data_key_destinataire_encrypted' => $invitation->data_key_encrypted,
                'permission' => $invitation->permission, 'expire_le' => $invitation->expire_le,
                'statut' => 'accepte', 'accepte_le' => now(),
                'element_ids' => $invitation->element_ids, 'family_group_id' => $invitation->family_group_id,
            ]);
            $invitation->update(['statut' => 'acceptee', 'traitee_le' => now()]);

            return $share;
        });
    }
}
