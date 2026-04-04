<?php

namespace App\Http\Controllers;

use App\Helpers\SessionHelper;
use App\Mail\InvitationAccepteeMail;
use App\Mail\InvitationPartageMail;
use App\Models\Coffre;
use App\Models\InvitationPartage;
use App\Models\ShareCoffre;
use App\Models\User;
use App\Services\Coffre\CleManagementService;
use App\Services\Crypto\Contracts\CryptoAsymmetricInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Random\RandomException;

class PartageController extends Controller
{
    public function __construct(
        private readonly CleManagementService $keyManagement,
        private readonly CryptoAsymmetricInterface $asymmetric,
    ) {}


    public function index(): View
    {
        $user = auth()->user();

        $partagesEnvoyes = ShareCoffre::with(['coffre', 'destinataire'])
            ->where('proprietaire_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $partagesRecus = ShareCoffre::with(['coffre', 'proprietaire'])
            ->where('destinataire_id', $user->id)
            ->where('statut', 'accepte')
            ->orderByDesc('created_at')
            ->get();

        $invitationsEnAttente = InvitationPartage::with('coffre')
            ->where('expediteur_id', $user->id)
            ->where('statut', 'en_attente')
            ->where('expire_le', '>', now())
            ->orderByDesc('created_at')
            ->get();

        $coffres = $user->coffres()->withCount('elements')->get();

        return view('partage.index', compact(
            'partagesEnvoyes',
            'partagesRecus',
            'invitationsEnAttente',
            'coffres'
        ));
    }


    /**
     * @throws RandomException
     * @throws \SodiumException
     */
    public function envoyer(Request $request): RedirectResponse
    {
        $request->validate([
            'coffre_id' => ['required', 'exists:coffres,id'],
            'email' => ['required', 'email', 'max:255'],
            'permission' => ['required', 'in:lecture,ecriture'],
        ]);

        $user = auth()->user();
        $coffre = Coffre::findOrFail($request->coffre_id);

        if ($coffre->user_id !== $user->id) {
            abort(403);
        }

        if ($request->email === $user->email) {
            return back()->withErrors(['email' => 'Vous ne pouvez pas partager avec vous-même.']);
        }

        $kek = SessionHelper::obtenirKek();

        $dataKey = $this->keyManagement->dechiffrerDataKeyCoffre(
            $coffre->data_key_encrypted,
            $kek
        );

        sodium_memzero($kek);

        $destinataire = User::where('email', $request->email)->first();

        $dataKeyChiffree = null;

        if ($destinataire) {
            $clePublique = $destinataire->clesUser?->cle_publique;

            if ($clePublique) {
                $dataKeyChiffree = $this->asymmetric->chiffrerAvecClePublique(
                    $dataKey,
                    $clePublique
                );
            }

            $partageExistant = ShareCoffre::where('coffre_id', $coffre->id)
                ->where('destinataire_id', $destinataire->id)
                ->first();

            if ($partageExistant) {
                sodium_memzero($dataKey);
                return back()->withErrors(['email' => 'Ce coffre est déjà partagé avec cet utilisateur.']);
            }
        }

        sodium_memzero($dataKey);

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        InvitationPartage::create([
            'coffre_id' => $coffre->id,
            'expediteur_id' => $user->id,
            'email_destinataire' => $request->email,
            'token_hash' => $tokenHash,
            'data_key_encrypted' => $dataKeyChiffree,
            'permission' => $request->permission,
            'statut' => 'en_attente',
            'expire_le' => now()->addHours(72),
        ]);

        Mail::to($request->email)->send(new InvitationPartageMail(
            $user,
            $request->email,
            $coffre->nom,
            $request->permission,
            route('partage.accepter', $token)
        ));

        return redirect()
            ->route('partage.index')
            ->with('toast', [
                'type' => 'success',
                'titre' => 'Invitation envoyée',
                'message' => "Un email a été envoyé à {$request->email}.",
            ]);
    }

    /**
     * @throws \SodiumException
     */
    public function accepter(string $token): RedirectResponse
    {
        $tokenHash = hash('sha256', $token);
        $invitation = InvitationPartage::where('token_hash', $tokenHash)
            ->where('statut', 'en_attente')
            ->where('expire_le', '>', now())
            ->firstOrFail();

        $user  = auth()->user();
        $coffre = $invitation->coffre;

        if ($invitation->email_destinataire !== $user->email) {
            return redirect()->route('dashboard')->with('toast', [
                'type' => 'error',
                'titre' => 'Invitation invalide',
                'message' => 'Cette invitation ne vous est pas destinée.',
            ]);
        }

        $dataKeyChiffree = $invitation->data_key_encrypted;

        if (!$dataKeyChiffree) {
            $kek = SessionHelper::obtenirKek();

            sodium_memzero($kek);

            $invitation->update(['statut' => 'expiree']);

            return redirect()->route('dashboard')->with('toast', [
                'type' => 'warning',
                'titre' => 'Invitation expirée',
                'message' => 'Demandez à l\'expéditeur de vous envoyer une nouvelle invitation.',
            ]);
        }

        ShareCoffre::create([
            'coffre_id' => $coffre->id,
            'proprietaire_id' => $invitation->expediteur_id,
            'destinataire_id' => $user->id,
            'data_key_destinataire_encrypted' => $dataKeyChiffree,
            'permission' => $invitation->permission,
            'statut' => 'accepte',
            'accepte_le' => now(),
        ]);

        $invitation->update([
            'statut' => 'acceptee',
            'traitee_le' => now(),
        ]);

        Mail::to($coffre->user->email)->send(new InvitationAccepteeMail(
            $coffre->user,
            auth()->user(),
            $coffre->nom,
        ));

        return redirect()
            ->route('dashboard')
            ->with('toast', [
                'type' => 'success',
                'titre' => 'Invitation acceptée',
                'message' => "Le coffre « {$coffre->nom} » est maintenant dans votre dashboard.",
            ]);
    }

    public function revoquer(ShareCoffre $share): RedirectResponse
    {
        $user = auth()->user();

        if ($share->proprietaire_id !== $user->id) {
            abort(403);
        }

        $nomDestinataire = $share->destinataire->name;
        $share->delete();

        return redirect()
            ->route('partage.index')
            ->with('toast', [
                'type'    => 'warning',
                'titre'   => 'Accès révoqué',
                'message' => "{$nomDestinataire} n'a plus accès à ce coffre.",
            ]);
    }

    public function annulerInvitation(InvitationPartage $invitation): RedirectResponse
    {
        if ($invitation->expediteur_id !== auth()->id()) {
            abort(403);
        }

        $invitation->update(['statut' => 'expiree']);

        return redirect()
            ->route('partage.index')
            ->with('toast', [
                'type'    => 'info',
                'titre'   => 'Invitation annulée',
                'message' => 'L\'invitation a été annulée.',
            ]);
    }
}
