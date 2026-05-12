<?php

namespace App\Http\Controllers;

use App\Mail\MembreFamilleAjouteMail;
use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\User;
use App\Services\Logs\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class FamilleController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $group = FamilyGroup::where('owner_id', $user->id)->with('members.user')->first();
        $membership = FamilyMember::where('user_id', $user->id)->with('group.owner')->first();

        return view('famille.index', compact('user', 'group', 'membership'));
    }

    public function creerGroupe(): RedirectResponse
    {
        $user = auth()->user();

        if (FamilyGroup::where('owner_id', $user->id)->exists()) {
            return back()->with('toast', ['type' => 'error', 'titre' => 'Erreur', 'message' => 'Vous avez déjà un groupe famille.']);
        }

        $group = FamilyGroup::create(['owner_id' => $user->id, 'nom' => 'Ma famille']);

        FamilyMember::create([
            'family_group_id' => $group->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        ActivityLogService::log('famille_groupe_cree', 'Groupe famille créé');

        return back()->with('toast', ['type' => 'success', 'titre' => 'Groupe créé', 'message' => 'Invitez maintenant vos membres.']);
    }

    public function inviter(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = auth()->user();
        $group = FamilyGroup::where('owner_id', $user->id)->with('members')->firstOrFail();

        if ($group->isFull()) {
            return back()->with('toast', ['type' => 'error', 'titre' => 'Limite atteinte', 'message' => 'Votre groupe est complet (6 membres maximum).']);
        }

        $destinataire = User::where('email', $request->email)->first();

        if (!$destinataire) {
            return back()->with('toast', ['type' => 'error', 'titre' => 'Utilisateur introuvable', 'message' => 'Cet email n\'est pas enregistré sur Soldier.']);
        }

        if ($destinataire->id === $user->id) {
            return back()->with('toast', ['type' => 'error', 'titre' => 'Erreur', 'message' => 'Vous ne pouvez pas vous inviter vous-même.']);
        }

        if (FamilyMember::where('family_group_id', $group->id)->where('user_id', $destinataire->id)->exists()) {
            return back()->with('toast', ['type' => 'error', 'titre' => 'Déjà membre', 'message' => 'Cet utilisateur est déjà dans votre groupe.']);
        }

        FamilyMember::create([
            'family_group_id' => $group->id,
            'user_id' => $destinataire->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        Mail::to($user->email)->send(new MembreFamilleAjouteMail($user, $destinataire));

        ActivityLogService::log('famille_membre_invite', "Membre ajouté : {$request->email}");

        return back()->with('toast', ['type' => 'success', 'titre' => 'Membre ajouté', 'message' => "{$destinataire->name} a été ajouté à votre groupe."]);
    }

    public function retirer(FamilyMember $member): RedirectResponse
    {
        $user = auth()->user();
        $group = FamilyGroup::where('owner_id', $user->id)->firstOrFail();

        if ($member->family_group_id !== $group->id || $member->role === 'owner') {
            abort(403);
        }

        $nom = $member->user->name;
        $member->delete();

        ActivityLogService::log('famille_membre_retire', "Membre retiré : {$nom}");

        return back()->with('toast', ['type' => 'warning', 'titre' => 'Membre retiré', 'message' => "{$nom} a été retiré du groupe."]);
    }

    public function quitter(): RedirectResponse
    {
        $user = auth()->user();
        $membership = FamilyMember::where('user_id', $user->id)->where('role', 'member')->firstOrFail();
        $membership->delete();

        ActivityLogService::log('famille_quitte', 'A quitté le groupe famille');

        return redirect()->route('famille.index')->with('toast', ['type' => 'info', 'titre' => 'Groupe quitté', 'message' => 'Vous avez quitté le groupe famille.']);
    }
}
