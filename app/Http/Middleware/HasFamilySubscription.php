<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasFamilySubscription
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->subscribed('famille')) {
            return redirect()->route('pricing')->with('toast', [
                'type' => 'warning',
                'titre' => 'Abonnement requis',
                'message' => 'Cette fonctionnalité est réservée au plan Famille.',
            ]);
        }

        return $next($request);
    }
}
