<?php

namespace App\Listeners;

use App\Models\FamilyGroup;
use App\Models\User;
use App\Services\Logs\ActivityLogService;
use Laravel\Cashier\Events\WebhookReceived;

class StripeEventListener
{
    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;
        $type = $payload['type'] ?? null;

        match ($type) {
            'customer.subscription.deleted' => $this->abonnementAnnule($payload),
            'customer.subscription.updated' => $this->abonnementMisAJour($payload),
            default => null,
        };
    }

    private function abonnementAnnule(array $payload): void
    {
        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;
        if (!$stripeCustomerId) return;

        $user = User::where('stripe_id', $stripeCustomerId)->first();
        if (!$user) return;

        $this->retirerMembresGroupe($user, 'annulation');
    }

    private function abonnementMisAJour(array $payload): void
    {
        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;
        $status = $payload['data']['object']['status'] ?? null;
        if (!$stripeCustomerId) return;

        if (in_array($status, ['canceled', 'unpaid', 'past_due', 'incomplete_expired'])) {
            $user = User::where('stripe_id', $stripeCustomerId)->first();
            if (!$user) return;
            $this->retirerMembresGroupe($user, $status);
        }
    }

    private function retirerMembresGroupe(User $user, string $raison): void
    {
        $group = FamilyGroup::where('owner_id', $user->id)->first();
        if (!$group) return;

        $count = $group->members()->where('role', 'member')->count();

        $group->members()->where('role', 'member')->delete();

        ActivityLogService::log(
            'famille_abonnement_expire',
            "Abonnement famille expiré ({$raison}) — {$count} membre(s) retiré(s)",
            $user->id
        );

        \Log::info("Stripe: abonnement famille expiré pour user {$user->id} — raison: {$raison} — {$count} membres retirés");
    }
}
