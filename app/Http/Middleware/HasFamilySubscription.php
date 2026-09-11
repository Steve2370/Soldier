<?php

namespace App\Http\Middleware;

use App\Models\FamilyMember;
use Closure;
use Illuminate\Http\Request;

class HasFamilySubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $membership = $user
            ? FamilyMember::with('group.owner')->where('user_id', $user->id)->first()
            : null;
        $billingUser = $membership?->group?->owner ?? $user;

        if (!$billingUser || !$billingUser->subscribed('famille')) {
            return redirect()->route('pricing')->with('toast', [
                'type' => 'warning',
                'titre' => 'Abonnement requis',
                'message' => 'Cette fonctionnalité est réservée au plan Famille.',
            ]);
        }

        return $next($request);
    }
}
