<?php

namespace App\Listeners;

use App\Models\FamilyGroup;
use App\Models\User;
use App\Services\Famille\FamilyAccessRevocationService;
use App\Services\Logs\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Events\WebhookReceived;

final readonly class StripeEventListener
{
    public function __construct(private FamilyAccessRevocationService $revocation) {}

    public function handle(WebhookReceived $event): void
    {
        $type = $event->payload['type'] ?? null;
        $subscription = $event->payload['data']['object'] ?? [];
        $status = $subscription['status'] ?? null;
        if ($type !== 'customer.subscription.deleted'
            && ! ($type === 'customer.subscription.updated'
                && in_array($status, ['canceled', 'unpaid', 'past_due', 'incomplete_expired'], true))) {
            return;
        }
        if (empty($subscription['customer']) || empty($subscription['id'])) {
            return;
        }

        DB::transaction(function () use ($subscription, $status): void {
            $user = User::where('stripe_id', $subscription['customer'])->lockForUpdate()->first();
            if (! $user || $user->family_granted_at !== null) {
                return;
            }

            // Received runs before Cashier updates/removes the local subscription.
            // Use the exact identity; unrelated products must not revoke Famille.
            if (! $user->subscriptions()->where('stripe_id', $subscription['id'])->where('type', 'famille')->exists()) {
                return;
            }
            if ($user->subscriptions()->where('type', 'famille')->where('stripe_id', '!=', $subscription['id'])
                ->get()->contains(fn ($other) => $other->valid())) {
                return;
            }

            foreach (FamilyGroup::where('owner_id', $user->id)->get() as $group) {
                $count = $this->revocation->removeAll($group);
                if ($count > 0) {
                    ActivityLogService::log('famille_abonnement_expire',
                        "Abonnement famille expiré ({$status}) — {$count} membre(s) retiré(s)", $user->id);
                }
            }
        });
    }
}
