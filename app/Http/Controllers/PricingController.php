<?php

namespace App\Http\Controllers;

use App\Services\Billing\Contracts\FamilyBillingGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function __construct(private readonly FamilyBillingGateway $billing) {}

    public function index(): View
    {
        $user = auth()->user();
        $abonnement = $user?->isFamilySubscriber() ?? false;

        return view('pricing.index', compact('abonnement'));
    }

    public function checkout(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('inscription')->with('toast', [
                'type' => 'info',
                'titre' => 'Connexion requise',
                'message' => 'Connectez-vous d\'abord, puis revenez sur la page Tarifs.',
            ]);
        }

        if ($user->isFamilySubscriber()) {
            return redirect()->route('pricing.portail');
        }

        if ($user->family_granted_at) {
            return redirect()->route('famille.index');
        }

        // An unpaid/incomplete subscription must be resolved, not duplicated.
        if ($user->subscriptions()->where('type', 'famille')
            ->whereNotIn('stripe_status', ['canceled', 'incomplete_expired'])->exists()) {
            return redirect()->route('pricing.portail');
        }

        return $this->billing->checkout($user);
    }

    public function succes(Request $request): View
    {
        // A return URL (or an arbitrary session_id) is not proof of payment.
        return view('pricing.succes', ['active' => $request->user()->hasFamilyPlan()]);
    }

    public function portail(Request $request): RedirectResponse
    {
        if (! $request->user()->stripe_id) {
            return redirect()->route('pricing')->with('toast', [
                'type' => 'info', 'titre' => 'Aucune facturation Stripe',
                'message' => 'Ce compte ne possède pas de compte de facturation Stripe.',
            ]);
        }

        return $this->billing->portal($request->user());
    }
}
