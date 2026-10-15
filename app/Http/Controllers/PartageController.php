<?php

namespace App\Http\Controllers;

use App\Helpers\SessionHelper;
use App\Http\Requests\SendShareInvitationRequest;
use App\Mail\InvitationAccepteeMail;
use App\Models\FamilyGroup;
use App\Models\InvitationPartage;
use App\Models\ShareCoffre;
use App\Services\Logs\ActivityLogService;
use App\Services\Notifications\Contracts\MailDeliveryInterface;
use App\Services\Partage\AcceptShareInvitation;
use App\Services\Partage\SendShareInvitations;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Random\RandomException;

class PartageController extends Controller
{
    public function __construct(
        private readonly SendShareInvitations $sendInvitations,
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
    public function envoyer(SendShareInvitationRequest $request): RedirectResponse
    {
        $kek = SessionHelper::obtenirKek();
        try {
            $result = $this->sendInvitations->send($request->user(), $request->validated(), $kek);
        } finally {
            sodium_memzero($kek);
        }

        ActivityLogService::log('partage_envoye', "{$result['created']} invitation(s) de partage créée(s)");

        return redirect()->route('partage.index')->with('toast', [
            'type' => $result['sent'] === $result['created'] ? 'success' : 'warning',
            'titre' => $result['sent'] === $result['created'] ? 'Invitations envoyées' : 'Envoi incomplet',
            'message' => "{$result['created']} invitation(s) créée(s), {$result['sent']} email(s) envoyé(s)."
                .($result['sent'] === $result['created'] ? '' : ' Annulez puis renvoyez les invitations non reçues lorsque le service est rétabli.'),
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
