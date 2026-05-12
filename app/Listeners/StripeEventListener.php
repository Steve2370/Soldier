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
            'customer.subscription.deleted',
            'customer.subscription.updated' => $this->gererAbonnement($payload),
            default => null,
        };
    }

    private function gererAbonnement(array $payload): void
    {
        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;
        $status = $payload['data']['object']['status'] ?? null;

        if (!$stripeCustomerId) return;

        $user = User::where('stripe_id', $stripeCustomerId)->first();
        if (!$user) return;

        if (in_array($status, ['canceled', 'unpaid', 'past_due']) || $payload['type'] === 'customer.subscription.deleted') {
            $group = FamilyGroup::where('owner_id', $user->id)->first();

            if ($group) {
                $group->members()->where('role', 'member')->delete();
                ActivityLogService::log('famille_abonnement_expire', 'Abonnement famille expiré — membres retirés', $user->id);
            }
        }
    }
}
