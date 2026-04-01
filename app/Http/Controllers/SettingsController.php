<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidMasterPasswordException;
use App\Helpers\SessionHelper;
use App\Http\Requests\Settings\ChangerMotDePasseRequest;
use App\Services\Auth\MfaService;
use App\Services\Coffre\CleManagementService;
use App\Services\Crypto\Contracts\EncryptionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private readonly CleManagementService $cleManagement,
        private readonly MfaService $mfaService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $mfaEmail = $user->mfa()->where('type', 'email')->first();
        $mfaTotp = $user->mfa()->where('type', 'totp')->first();
//        $passKeys = $user->passKeys()->orderByDesc('created_at')->get();

        return view('settings.index', compact('user','mfaEmail', 'mfaTotp'));
    }

    public function activerMfaEmail(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $user->mfa()->updateOrCreate(
            ['type' => 'email'],
            ['actif' => true, 'active_le' => now()]
        );

        $this->mfaService->envoyerCodeEmail($user);

        return redirect()->route('settings')
            ->with('toast', [
                'type' => 'success',
                'titre' => 'MFA Email activé',
                'message' => 'Un code de vérification vous a été envoyé.',
            ]);
    }

    public function desactiverMfaEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'master_password' => ['required', 'string'],
        ]);

        $user = auth()->user();
        try {
            $this->cleManagement->deverouillerCles($user, $request->master_password);
        } catch (InvalidMasterPasswordException) {
            return back()->withErrors(['master_password' => 'Le master mot de passe est incorrect.'])
                ->with('tab', 'securite');
        }

        $user->mfa()->where('type', 'email')->update(['actif' => false]);

        return redirect()->route('settings')
            ->with('toast', [
                'type' => 'warning',
                'titre' => 'MFA Email désactivé',
                'message' => 'Votre compte est moins protégé sans le MFA.',
            ]);
    }

    public function configurerTopt(): JsonResponse
    {
        $user = auth()->user();
        $donnees = $this->mfaService->genererSecretTotp($user);
        session(['totp_secret_pending' => $donnees['secret']]);

        return response()->json([
            'qr_url' => $donnees['qr_url'],
            'secret' => $donnees['secret'],
            'otpauth_url' => $donnees['otpauth_url'],
        ]);
    }

    /**
     * @throws \SodiumException
     */
    public function validerTopt(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $user = auth()->user();
        $secret = session('totp_secret_pending');

        if (!$secret) {
            return back()->withErrors(['code' => 'Session expirée. Recommencez.']);
        }

        $kek = SessionHelper::obtenirKek();
        $secretChiffre = app(EncryptionServiceInterface::class)->encrypt($secret, $kek);
        sodium_memzero($kek);

        $user->mfa()->updateOrCreate(
            ['type' => 'totp'],
            [
                'actif' => true,
                'totp_secret_chiffre' => json_encode($secretChiffre),
                'active_le' => now(),
            ]);
        session()->forget('totp_secret_pending');

        $codes = $this->mfaService->genererCodesRecuperation($user);
        session(['codes_recuperation_afficher' => $codes]);

        return redirect()->route('settings')
            ->with('toast', [
                'type' => 'success',
                'titre' => 'Authenticator configuré',
                'message' => 'Sauvegardez vos codes de récupération.',
            ]);
    }

    /**
     * @throws \Throwable
     * @throws \SodiumException
     */
    public function changerMotDePasse(ChangerMotDePasseRequest $request): RedirectResponse
    {
        $user = auth()->user();

        try {
            $this->cleManagement->changerMasterPassword(
                $user,
                $request->validated('ancien_master_password'),
                $request->validated('nouveau_master_password'),
            );
        } catch (InvalidMasterPasswordException) {
            return back()
                ->withErrors(['ancien_master_password' => 'L\'ancien master password est incorrect.'])
                ->with('tab', 'securite');
        }

        $cles = $this->cleManagement->deverouillerCles($user, $request->validated('nouveau_master_password'));
        SessionHelper::deverouiller($cles['kek'], $cles['cle_privee']);
        sodium_memzero($cles['kek']);

        return redirect()->route('settings')
            ->with('toast', [
                'type' => 'success',
                'titre' => 'Master password modifié',
                'message' => 'Votre coffre a été re-chiffré avec le nouveau mot de passe.',
            ]);
    }

    public function changerMotDePasseCompte(Request $request): RedirectResponse
    {
        $request->validate([
            'mot_de_passe_actuel' => ['required', 'string'],
            'nouveau_mot_de_passe' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = auth()->user();

        if (!Hash::check($request->mot_de_passe_actuel, $user->password)) {
            return back()->withErrors(['mot_de_passe_actuel' => 'Le mot de passe actuel est incorrect.'])
                ->with('tab', 'compte');
        }

        $user->update(['password' => Hash::make($request->nouveau_mot_de_passe)]);

        return redirect()->route('settings')
            ->with('toast', [
               'type' => 'success',
                'titre' => 'Mot de passe modifié',
                'message' => 'Votre mot de passe de connexion a été mis à jour.',
            ]);
    }

    public function changerAvatar(Request $request): RedirectResponse
    {
        \Log::info('changerAvatar appelé', [
            'hasFile' => $request->hasFile('avatar'),
            'allKeys' => array_keys($request->all()),
            'files' => array_keys($request->allFiles()),
        ]);

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $user = auth()->user();
        if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
            \Storage::disk('public')->delete($user->avatar);
        }
        $chemin = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $chemin]);

        return redirect()->route('settings')->with('toast', [
            'type' => 'success',
            'titre' => 'Photo mise à jour',
            'message' => 'Votre photo de profil a été modifiée.',
        ]);
    }

    public function supprimerAvatar(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
            \Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return redirect()->route('settings')->with('toast', [
            'type' => 'info',
            'titre' => 'Photo supprimée',
            'message' => 'Votre photo de profil a été supprimée.',
        ]);
    }
}
