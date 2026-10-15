<?php

namespace App\Services\Partage;

use App\Mail\InvitationPartageMail;
use App\Models\Coffre;
use App\Models\FamilyGroup;
use App\Models\InvitationPartage;
use App\Models\ShareCoffre;
use App\Models\User;
use App\Services\Coffre\CleManagementService;
use App\Services\Crypto\Contracts\CryptoAsymmetricInterface;
use App\Services\Notifications\Contracts\MailDeliveryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class SendShareInvitations
{
    public function __construct(
        private ShareAudienceResolver $audiences,
        private CleManagementService $keys,
        private CryptoAsymmetricInterface $asymmetric,
        private MailDeliveryInterface $mailDelivery,
    ) {}

    /** @return array{created: int, sent: int} */
    public function send(User $sender, array $input, string $kek): array
    {
        $dataKey = null;
        try {
            $vault = Coffre::findOrFail($input['coffre_id']);
            abort_unless($vault->user_id === $sender->id, 403);
            $elementIds = collect($input['element_ids'])->map(fn ($id) => (int) $id)->unique()->values();
            if ($vault->elements()->whereIn('id', $elementIds)->count() !== $elementIds->count() || $elementIds->isEmpty()) {
                throw ValidationException::withMessages(['element_ids' => 'Un ou plusieurs éléments ne correspondent pas à ce coffre.']);
            }
            $family = (bool) ($input['partage_groupe'] ?? false);
            $audience = $this->audiences->resolve($sender, $family, $input['email'] ?? null);
            if (! $family && FamilyGroup::where('coffre_id', $vault->id)
                ->whereDoesntHave('members', fn ($query) => $query->where('user_id', $audience['recipients']->sole()->id))->exists()) {
                throw ValidationException::withMessages(['email' => 'Le coffre famille est réservé aux membres de ce groupe. Choisissez un coffre personnel pour partager avec une autre personne.']);
            }
            $existing = ShareCoffre::where('coffre_id', $vault->id)->pluck('destinataire_id');
            $recipients = $audience['recipients']->reject(fn (User $user) => $existing->contains($user->id));
            if ($recipients->isEmpty()) {
                throw ValidationException::withMessages([$family ? 'partage_groupe' : 'email' => $family
                    ? 'Ce coffre est déjà partagé avec tous les membres de votre groupe.'
                    : 'Ce coffre est déjà partagé avec cet utilisateur.']);
            }

            $dataKey = $this->keys->dechiffrerDataKeyCoffre($vault->data_key_encrypted, $kek);
            // Persist the whole batch before attempting delivery, never a partial batch of envelopes.
            $messages = DB::transaction(function () use ($recipients, $sender, $vault, $elementIds, $input, $audience, $dataKey): array {
                $messages = [];
                foreach ($recipients as $recipient) {
                    $token = bin2hex(random_bytes(32));
                    InvitationPartage::create([
                        'family_group_id' => $audience['family_group_id'],
                        'coffre_id' => $vault->id,
                        'expediteur_id' => $sender->id,
                        'email_destinataire' => $recipient->email,
                        'token_hash' => hash('sha256', $token),
                        'data_key_encrypted' => $this->asymmetric->chiffrerAvecClePublique($dataKey, $recipient->clesUser->public_key),
                        'permission' => $input['permission'],
                        'statut' => 'en_attente',
                        'expire_le' => now()->addHours(72),
                        'element_ids' => $elementIds->all(),
                    ]);
                    $messages[] = [$recipient->email, new InvitationPartageMail(
                        $sender, $recipient->email, $vault->nom, $input['permission'], route('partage.accepter', $token)
                    )];
                }

                return $messages;
            });
        } finally {
            sodium_memzero($kek);
            if ($dataKey !== null) {
                sodium_memzero($dataKey);
            }
        }

        $sent = 0;
        foreach ($messages as [$email, $message]) {
            $sent += (int) $this->mailDelivery->send($email, $message);
        }

        return ['created' => count($messages), 'sent' => $sent];
    }
}
