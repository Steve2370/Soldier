<?php

namespace App\Http\Controllers;

use App\Helpers\SessionHelper;
use App\Mail\InvitationAccepteeMail;
use App\Mail\InvitationPartageMail;
use App\Models\Coffre;
use App\Models\FamilyGroup;
use App\Models\InvitationPartage;
use App\Models\ShareCoffre;
use App\Models\User;
use App\Services\Coffre\CleManagementService;
use App\Services\Crypto\Contracts\CryptoAsymmetricInterface;
use App\Services\Logs\ActivityLogService;
use App\Services\Notifications\Contracts\MailDeliveryInterface;
use App\Services\Partage\AcceptShareInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Random\RandomException;

class PartageController extends Controller
{
    public function __construct(
        private readonly CleManagementService $keyManagement,
        private readonly CryptoAsymmetricInterface $asymmetric,
        private readonly MailDeliveryInterface $mailDelivery,
        private readonly AcceptShareInvitation $acceptInvitation,
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
            ->actifs()
            ->orderByDesc('created_at')
            ->get();

        $invitationsEnAttente = InvitationPartage::with('coffre')
            ->where('expediteur_id', $user->id)
            ->where('statut', 'en_attente')
            ->where('expire_le', '>', now())
            ->orderByDesc('created_at')
            ->get();

        $coffres = $user->coffres()->with('elements')->withCount('elements')->get();
        $familyGroup = FamilyGroup::where('owner_id', $user->id)
            ->with('members.user')
            ->first();
        $familyMembers = $familyGroup ? $familyGroup->members->where('role', 'member') : collect();

        return view('partage.index', compact(
            'partagesEnvoyes',
            'partagesRecus',
            'invitationsEnAttente',
            'coffres',
            'familyGroup',
            'familyMembers'
        ));
    }

    /**
     * @throws RandomException
     * @throws \SodiumException
     */
    public function envoyer(Request $request): RedirectResponse
    {
        if ($request->partage_groupe == '1') {
            $request->validate([
                'coffre_id' => ['required', 'exists:coffres,id'],
                'permission' => ['required', 'in:lecture,ecriture'],
                'element_ids' => ['required', 'array', 'min:1'],
                'element_ids.*' => ['integer', 'exists:elements_coffres,id'],
            ]);
        } else {
            $request->validate([
                'coffre_id' => ['required', 'exists:coffres,id'],
                'email' => ['required', 'email', 'max:255'],
                'permission' => ['required', 'in:lecture,ecriture'],
                'element_ids' => ['required', 'array', 'min:1'],
                'element_ids.*' => ['integer', 'exists:elements_coffres,id'],
            ]);
        }

        $user = auth()->user();
        $coffre = Coffre::findOrFail($request->coffre_id);
        if ($coffre->user_id !== $user->id) {
            abort(403);
        }

        $kek = SessionHelper::obtenirKek();
        $dataKey = $this->keyManagement->dechiffrerDataKeyCoffre($coffre->data_key_encrypted, $kek);
        sodium_memzero($kek);

        $elementIds = collect($request->element_ids)->map(fn ($id) => (int) $id)->unique()->values();
        $elements = $coffre->elements()->whereIn('id', $elementIds)->get();
        if ($elements->count() !== $elementIds->count()) {
            sodium_memzero($dataKey);

            return back()->withErrors(['element_ids' => 'Un ou plusieurs éléments ne correspondent pas à ce coffre.']);
        }

        if ($request->partage_groupe == '1') {
            $familyGroup = FamilyGroup::where('owner_id', $user->id)
                ->with('members.user.clesUser')
                ->first();

            if (! $familyGroup) {
                sodium_memzero($dataKey);

                return back()->withErrors(['email' => 'Aucun groupe famille trouvé.']);
            }

            $count = 0;
            $sent = 0;

            foreach ($familyGroup->members->where('role', 'member') as $membre) {
                $destinataire = $membre->user;
                $clePublique = $destinataire->clesUser?->public_key;
                if (! $clePublique) {
                    continue;
                }

                $dataKeyChiffree = $this->asymmetric->chiffrerAvecClePublique($dataKey, $clePublique);

                if (ShareCoffre::where('coffre_id', $coffre->id)->where('destinataire_id', $destinataire->id)->exists()) {
                    continue;
                }

                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);

                InvitationPartage::create([
                    'family_group_id' => $familyGroup->id,
                    'coffre_id' => $coffre->id,
                    'expediteur_id' => $user->id,
                    'email_destinataire' => $destinataire->email,
                    'token_hash' => $tokenHash,
                    'data_key_encrypted' => $dataKeyChiffree,
                    'permission' => $request->permission,
                    'statut' => 'en_attente',
                    'expire_le' => now()->addHours(72),
                    'element_ids' => $elementIds->all(),
                ]);

                $delivered = $this->mailDelivery->send($destinataire->email, new InvitationPartageMail(
                    $user,
                    $destinataire->email,
                    $coffre->nom,
                    $request->permission,
                    route('partage.accepter', $token)
                ));
                $count++;
                $sent += (int) $delivered;
            }

            sodium_memzero($dataKey);
            ActivityLogService::log('partage_envoye', "Partage groupe famille — coffre : {$coffre->nom} — {$count} membres");

            return redirect()->route('partage.index')->with('toast', [
                'type' => $sent === $count ? 'success' : 'warning',
                'titre' => 'Invitations créées',
                'message' => "{$count} invitation(s) créée(s), {$sent} email(s) envoyé(s).",
            ]);
        }

        if ($request->email === $user->email) {
            sodium_memzero($dataKey);

            return back()->withErrors(['email' => 'Vous ne pouvez pas partager avec vous-même.']);
        }

        $destinataire = User::where('email', $request->email)->first();
        $dataKeyChiffree = null;

        if ($destinataire) {
            $clePublique = $destinataire->clesUser?->public_key;
            if ($clePublique) {
                $dataKeyChiffree = $this->asymmetric->chiffrerAvecClePublique($dataKey, $clePublique);
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
            'element_ids' => $elementIds->all(),
        ]);

        $sent = $this->mailDelivery->send($request->email, new InvitationPartageMail(
            $user,
            $request->email,
            $coffre->nom,
            $request->permission,
            route('partage.accepter', $token)
        ));

        ActivityLogService::log('partage_envoye', "Partage envoyé à {$request->email} — coffre : {$coffre->nom}");

        return redirect()->route('partage.index')->with('toast', [
            'type' => $sent ? 'success' : 'warning',
            'titre' => $sent ? 'Invitation envoyée' : 'Envoi indisponible',
            'message' => $sent ? "Un email a été envoyé à {$request->email}." : 'L’invitation est créée mais son email n’a pas pu être envoyé. Annulez-la puis renvoyez-la lorsque le service est rétabli.',
        ]);
    }

    /**
     * @throws \SodiumException
     */
    public function accepter(string $token): RedirectResponse
    {
        $user = auth()->user();
        $share = $this->acceptInvitation->accept($user, $token);
        $coffre = $share->coffre;
        if ($share->wasRecentlyCreated && $share->proprietaire) {
            $this->mailDelivery->send($share->proprietaire->email, new InvitationAccepteeMail($share->proprietaire, $user, $coffre->nom));
        }
        ActivityLogService::log('partage_accepte', "Partage accepté — coffre : {$coffre->nom}");

        return redirect()->route('dashboard')->with('toast', [
            'type' => 'success', 'titre' => 'Invitation acceptée', 'message' => "Le coffre « {$coffre->nom} » est maintenant dans votre dashboard.",
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

        ActivityLogService::log('partage_revoque', "Accès révoqué pour {$nomDestinataire}");

        return redirect()
            ->route('partage.index')
            ->with('toast', [
                'type' => 'warning',
                'titre' => 'Accès révoqué',
                'message' => "{$nomDestinataire} n'a plus accès à ce coffre.",
            ]);
    }

    public function annulerInvitation(InvitationPartage $invitation): RedirectResponse
    {
        if ($invitation->expediteur_id !== auth()->id()) {
            abort(403);
        }

        $invitation->update(['statut' => 'expiree']);

        ActivityLogService::log('invitation_annulee', "Invitation annulée pour {$invitation->email_destinataire}");

        return redirect()
            ->route('partage.index')
            ->with('toast', [
                'type' => 'info',
                'titre' => 'Invitation annulée',
                'message' => 'L\'invitation a été annulée.',
            ]);
    }
}
