<?php

namespace App\Http\Controllers;

use App\Helpers\SessionHelper;
use App\Mail\MembreFamilleAjouteMail;
use App\Mail\MembreInviteFamilleMail;
use App\Models\Coffre;
use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\ShareCoffre;
use App\Models\User;
use App\Services\Coffre\CleManagementService;
use App\Services\Coffre\CoffreService;
use App\Services\Crypto\RsaCryptoService;
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
        $membership = FamilyMember::where('user_id', $user->id)->with('group.owner.familyGroup')->first();

        $secretsPartages = collect();
        if ($membership || $group) {
            $familyGroup = $group ?? $membership->group;

            if ($familyGroup?->coffre_id) {
                $secretsPartages = ShareCoffre::with(['coffre', 'proprietaire'])
                    ->where('coffre_id', $familyGroup->coffre_id)
                    ->where('statut', 'accepte')
                    ->get();

                if ($group) {
                    $memberIds = $group->members->pluck('user_id')->toArray();
                    $secretsSupplementaires = ShareCoffre::with(['coffre', 'proprietaire', 'destinataire'])
                        ->where('proprietaire_id', $user->id)
                        ->whereIn('destinataire_id', $memberIds)
                        ->where('statut', 'accepte')
                        ->whereNotIn('coffre_id', [$familyGroup->coffre_id])
                        ->get();
                    $secretsPartages = $secretsPartages->merge($secretsSupplementaires)->unique('id');
                }
            }
        }

        return view('famille.index', compact('user', 'group', 'membership', 'secretsPartages'));
    }

    /**
     * @throws \SodiumException
     */
    public function creerGroupe(): RedirectResponse
    {
        $user = auth()->user();

        if (FamilyGroup::where('owner_id', $user->id)->exists()) {
            return back()->with('toast', ['type' => 'error', 'titre' => 'Erreur', 'message' => 'Vous avez déjà un groupe famille.']);
        }

        $kek = SessionHelper::obtenirKek();
        $coffre = app(CoffreService::class)->creerCoffre($user, [
            'nom' => 'Famille',
            'couleur' => '#2d9fd4',
        ], $kek);
        sodium_memzero($kek);

        $group = FamilyGroup::create([
            'owner_id' => $user->id,
            'nom' => 'Ma famille',
            'coffre_id' => $coffre->id,
        ]);

        FamilyMember::create([
            'family_group_id' => $group->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        ActivityLogService::log('famille_groupe_cree', 'Groupe famille créé avec coffre familial');

        return back()->with('toast', ['type' => 'success', 'titre' => 'Groupe créé !', 'message' => 'Votre coffre familial est prêt. Invitez vos membres.']);
    }

    /**
     * @throws \SodiumException
     */
    public function inviter(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user  = auth()->user();
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

        if ($group->coffre_id) {
            $coffre = Coffre::find($group->coffre_id);
            $kek  = SessionHelper::obtenirKek();
            $dataKey = app(CleManagementService::class)
                ->dechiffrerDataKeyCoffre($coffre->data_key_encrypted, $kek);
            sodium_memzero($kek);

            $clePublique = $destinataire->clesUser?->public_key;
            $dataKeyChiffree = null;

            if ($clePublique) {
                $dataKeyChiffree = app(RsaCryptoService::class)
                    ->chiffrerAvecClePublique($dataKey, $clePublique);
            }
            sodium_memzero($dataKey);

            if ($dataKeyChiffree) {
                ShareCoffre::create([
                    'coffre_id' => $coffre->id,
                    'proprietaire_id' => $user->id,
                    'destinataire_id' => $destinataire->id,
                    'data_key_destinataire_encrypted' => $dataKeyChiffree,
                    'permission' => 'ecriture',
                    'statut' => 'accepte',
                    'accepte_le' => now(),
                    'element_ids' => null,
                ]);
            }
        }

        Mail::to($user->email)->send(new MembreFamilleAjouteMail($user, $destinataire));
        Mail::to($destinataire->email)->send(new MembreInviteFamilleMail($user, $destinataire));

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
