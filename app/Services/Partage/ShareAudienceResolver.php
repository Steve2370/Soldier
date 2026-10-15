<?php

namespace App\Services\Partage;

use App\Models\FamilyGroup;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class ShareAudienceResolver
{
    /** @return array{family_group_id: ?int, recipients: Collection<int, User>} */
    public function resolve(User $sender, bool $family, ?string $email): array
    {
        if (! $family) {
            $email = mb_strtolower(trim($email ?? ''));
            if ($email === mb_strtolower($sender->email)) {
                throw ValidationException::withMessages(['email' => 'Vous ne pouvez pas partager avec vous-même.']);
            }
            $recipient = User::with('clesUser')->where('email', $email)->first();
            if (! $recipient?->clesUser?->public_key) {
                throw ValidationException::withMessages(['email' => 'Ce destinataire doit créer son compte et initialiser son coffre avant de recevoir un partage.']);
            }

            return ['family_group_id' => null, 'recipients' => collect([$recipient])];
        }

        abort_unless($sender->hasFamilyPlan(), 403);
        $group = FamilyGroup::where('owner_id', $sender->id)->with('members.user.clesUser')->first();
        $recipients = $group?->members->where('role', 'member')->pluck('user')->filter()
            ->reject(fn (User $user) => $user->id === $sender->id)->unique('id')->values() ?? collect();
        if ($recipients->isEmpty()) {
            throw ValidationException::withMessages(['partage_groupe' => 'Votre groupe famille doit contenir au moins un autre membre.']);
        }
        if ($recipients->contains(fn (User $user) => ! $user->clesUser?->public_key)) {
            throw ValidationException::withMessages(['partage_groupe' => 'Chaque membre doit initialiser son coffre avant de recevoir un partage.']);
        }

        return ['family_group_id' => $group->id, 'recipients' => $recipients];
    }
}
