<?php

namespace App\Http\Controllers;

use App\Models\Passkey;
use App\Services\Auth\PasskeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Random\RandomException;
use Throwable;
use Webauthn\Exception\WebauthnException;

/**
 * Ne fait plus aucune vérification cryptographique elle-même : toute la logique
 * d'enregistrement/vérification WebAuthn (parsing CBOR, vérification de signature,
 * anti-rejeu du compteur) est déléguée à PasskeyService (SRP + DIP).
 */
class PasskeyController extends Controller
{
    public function __construct(
        private readonly PasskeyService $passkeyService,
    ) {}

    /**
     * @throws RandomException
     */
    public function optionsInscription(Request $request): Response
    {
        $options = $this->passkeyService->optionsInscription(auth()->user());

        session(['passkey_challenge' => $options['challenge']]);

        return response($options['json'], 200)->header('Content-Type', 'application/json');
    }

    public function inscrire(Request $request): JsonResponse
    {
        $request->validate([
            'credential' => ['required', 'array'],
            'nom' => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $challenge = session('passkey_challenge');

        if (!$challenge) {
            return response()->json(['error' => 'Challenge expiré.'], 422);
        }

        try {
            $source = $this->passkeyService->verifierInscription(
                json_encode($request->input('credential')),
                $challenge,
                $user,
            );

            $nom = $request->input('nom') ?: $this->detecterNomAppareil($request);

            Passkey::create([
                'user_id' => $user->id,
                'nom' => $nom,
                'credential_id' => $this->passkeyService->credentialIdString($source),
                'cle_publique' => $this->passkeyService->serialiserSource($source),
                'compteur' => $source->counter,
                'type_authenticator' => $source->attestationType,
                'algorithme_cose' => null,
                'derniere_utilisation' => now(),
            ]);

            session()->forget('passkey_challenge');

            return response()->json(['success' => true, 'nom' => $nom]);
        } catch (WebauthnException $e) {
            Log::warning('Échec de vérification WebAuthn à l\'enregistrement d\'une passkey', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'La vérification de la passkey a échoué.'], 422);
        } catch (Throwable $e) {
            Log::error('Erreur inattendue lors de l\'enregistrement d\'une passkey', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Une erreur est survenue.'], 422);
        }
    }

    /**
     * @throws RandomException
     */
    public function optionsConnexion(Request $request): Response
    {
        $options = $this->passkeyService->optionsConnexion();

        session()->put('passkey_challenge_auth', $options['challenge']);
        session()->save();

        return response($options['json'], 200)->header('Content-Type', 'application/json');
    }

    public function connecter(Request $request): JsonResponse
    {
        session()->start();
        $challenge = session('passkey_challenge_auth');

        $request->validate([
            'credential' => ['required', 'array'],
        ]);

        if (!$challenge) {
            return response()->json(['error' => 'Challenge expiré.'], 422);
        }

        $credentialId = $request->input('credential.id');
        $passkey = Passkey::where('credential_id', $credentialId)->first();

        if (!$passkey) {
            return response()->json(['error' => 'Passkey non reconnu.'], 422);
        }

        try {
            $sourceStockee = $this->passkeyService->desserialiserSource($passkey->cle_publique);

            $sourceMiseAJour = $this->passkeyService->verifierConnexion(
                json_encode($request->input('credential')),
                $challenge,
                $sourceStockee,
            );

            $passkey->update([
                'cle_publique' => $this->passkeyService->serialiserSource($sourceMiseAJour),
                'compteur' => $sourceMiseAJour->counter,
                'derniere_utilisation' => now(),
            ]);

            session()->forget('passkey_challenge_auth');

            $user = $passkey->user;

            Auth::login($user);
            session()->regenerate();
            session(['oauth_login' => true, 'passkey_login' => true]);

            return response()->json([
                'success' => true,
                'redirect' => route('oauth.master-password'),
            ]);
        } catch (WebauthnException $e) {
            Log::warning('Échec de vérification WebAuthn à la connexion par passkey', [
                'passkey_id' => $passkey->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Authentification par passkey refusée.'], 422);
        } catch (Throwable $e) {
            Log::error('Erreur inattendue lors de l\'authentification par passkey', [
                'passkey_id' => $passkey->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Une erreur est survenue.'], 422);
        }
    }

    public function supprimer(Request $request, Passkey $passkey): RedirectResponse
    {
        if ($passkey->user_id !== auth()->id()) {
            abort(403);
        }

        $passkey->delete();

        return redirect()->route('settings')->with('toast', [
            'type' => 'success',
            'titre' => 'Passkey supprimé',
            'message' => "« {$passkey->nom} » a été supprimé.",
        ]);
    }

    private function detecterNomAppareil(Request $request): string
    {
        $ua = $request->userAgent() ?? '';

        if (str_contains($ua, 'iPhone')) return 'iPhone';
        if (str_contains($ua, 'iPad')) return 'iPad';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'Windows Phone')) return 'Windows Phone';
        if (str_contains($ua, 'BlackBerry')) return 'BlackBerry';
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Macintosh')) return 'Mac';
        if (str_contains($ua, 'Linux')) return 'Linux';
        if (str_contains($ua, 'CrOS')) return 'Chrome OS';
        return 'Appareil inconnu';
    }
}
