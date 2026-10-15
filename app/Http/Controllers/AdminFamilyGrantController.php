<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Admin\FamilyGrantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminFamilyGrantController extends Controller
{
    public function store(Request $request, User $user, FamilyGrantService $grants): RedirectResponse
    {
        $grants->setGranted($request->user(), $user, true);

        return redirect()->route('admin.index', ['tab' => 'users'])->with('toast', [
            'type' => 'success', 'titre' => 'Accès Famille offert',
            'message' => "{$user->name} dispose du forfait Famille offert, sans facturation Stripe.",
        ]);
    }

    public function destroy(Request $request, User $user, FamilyGrantService $grants): RedirectResponse
    {
        $request->validate(['confirmation' => ['accepted']]);
        $grants->setGranted($request->user(), $user, false);

        return redirect()->route('admin.index', ['tab' => 'users'])->with('toast', [
            'type' => 'success', 'titre' => 'Accès offert retiré',
            'message' => 'Seul l’accès offert a été retiré. Un éventuel abonnement payé reste inchangé.',
        ]);
    }
}
