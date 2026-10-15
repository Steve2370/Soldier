<?php

namespace App\Services\Billing;

use App\Models\User;
use App\Services\Billing\Contracts\FamilyBillingGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;

final class CashierFamilyBillingGateway implements FamilyBillingGateway
{
    public function checkout(User $user): RedirectResponse
    {
        $price = config('services.stripe.price_famille');
        if (! is_string($price) || ! preg_match('/^price_[a-zA-Z0-9]+$/', $price)
            || ! config('cashier.secret') || ! config('cashier.webhook.secret')) {
            $this->unavailable();
        }

        try {
            return $user->newSubscription('famille', $price)->checkout([
                'success_url' => route('pricing.succes').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('pricing'),
            ])->redirect();
        } catch (ApiErrorException $exception) {
            // Stripe messages may contain customer or request details; do not expose them.
            Log::warning('Stripe checkout unavailable', ['exception_type' => $exception::class]);
            $this->unavailable();
        }
    }

    public function portal(User $user): RedirectResponse
    {
        if (! $user->stripe_id || ! config('cashier.secret')) {
            $this->unavailable();
        }

        try {
            return $user->redirectToBillingPortal(route('pricing'));
        } catch (ApiErrorException $exception) {
            Log::warning('Stripe portal unavailable', ['exception_type' => $exception::class]);
            $this->unavailable();
        }
    }

    private function unavailable(): never
    {
        throw ValidationException::withMessages(['billing' => 'Le service de paiement est indisponible pour le moment. Aucun accès payé n’a été activé. Réessayez plus tard ou contactez le support.']);
    }
}
