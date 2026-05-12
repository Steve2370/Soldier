<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $abonnement = $user?->subscribed('famille') ?? false;

        return view('pricing.index', compact('abonnement'));
    }

    public function checkout(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('inscription')->with('toast', [
                'type' => 'info',
                'titre' => 'Connexion requise',
                'message' => 'Connectez-vous d\'abord, puis revenez sur la page Tarifs.',
            ]);
        }

        if ($user->subscribed('famille')) {
            return redirect()->route('pricing.portail');
        }

        return $user->newSubscription('famille', env('STRIPE_PRICE_FAMILLE'))
            ->checkout([
                'success_url' => route('pricing.succes') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('pricing'),
            ]);
    }

    public function succes(Request $request): View
    {
        return view('pricing.succes');
    }

    public function portail(Request $request): RedirectResponse
    {
        return $request->user()->redirectToBillingPortal(route('pricing'));
    }
}
