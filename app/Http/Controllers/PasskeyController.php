<?php

namespace App\Http\Controllers;

use App\Models\Passkey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Random\RandomException;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAssertionResponse;

class PasskeyController extends Controller
{
    private string $rpId;
    private string $rpName;
    private string $origin;

    public function __construct()
    {
        $this->rpId = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
        $this->rpName = config('app.name', 'Soldier');
        $this->origin = config('app.url');
    }

    /**
     * @throws RandomException
     */
    public function optionsInscription(Request $request): JsonResponse
    {
        $user = auth()->user();
        $rpEntity = PublicKeyCredentialRpEntity::create(
            name: $this->rpName,
            id: $this->rpId,
        );

        $userEntity = PublicKeyCredentialUserEntity::create(
            name: $user->email,
            id: (string) $user->id,
            displayName: $user->name,
        );

        $challenge = random_bytes(32);
        $options = PublicKeyCredentialCreationOptions::create(
            rp: $rpEntity,
            user: $userEntity,
            challenge: $challenge,
            pubKeyCredParams: [
                PublicKeyCredentialParameters::create('public-key', -7),
                PublicKeyCredentialParameters::create('public-key', -257),
            ],
            authenticatorSelection: AuthenticatorSelectionCriteria::create(
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
                residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED,
            ),
            timeout: 60000,
        );
        session(['passkey_challenge' => base64_encode($challenge)]);
        return response()->json($options);
    }

    public function inscrire(Request $request): JsonResponse
    {
        $request->validate([
            'credential' => ['required', 'array'],
            'nom' => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $challenge = base64_decode(session('passkey_challenge'));

        if (!$challenge) {
            return response()->json(['error' => 'Challenge expiré.'], 422);
        }

        try {
            $credential = $request->input('credential');
            $clientDataJSON = base64_decode($credential['response']['clientDataJSON']);
            $attestationObject = base64_decode($credential['response']['attestationObject']);
            $credentialId = $credential['id'];
            $clientData = json_decode($clientDataJSON, true);
            $receivedChallenge = base64_decode(strtr($clientData['challenge'], '-_', '+/'));

            if (!hash_equals($challenge, $receivedChallenge)) {
                return response()->json(['error' => 'Challenge invalide.'], 422);
            }

            if ($clientData['origin'] !== $this->origin) {
                return response()->json(['error' => 'Origine invalide.'], 422);
            }

            $attestation = $this->parseAttestationObject($attestationObject);
            $nom = $request->input('nom') ?: $this->detecterNomAppareil($request);

            PassKey::create([
                'user_id' => $user->id,
                'nom' => $nom,
                'credential_id' => $credentialId,
                'cle_publique' => json_encode($attestation['publicKey']),
                'compteur' => $attestation['signCount'] ?? 0,
                'type_authenticator' => $attestation['fmt'] ?? 'none',
                'algorithme_cose' => $attestation['alg'] ?? -7,
                'derniere_utilisation' => now(),
            ]);

            session()->forget('passkey_challenge');
            return response()->json(['success' => true, 'nom' => $nom]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur : ' . $e->getMessage()], 422);
        }
    }

    /**
     * @throws RandomException
     */
    public function optionsConnexion(Request $request): JsonResponse
    {
        $challenge = random_bytes(32);
        $options = PublicKeyCredentialRequestOptions::create(
           challenge: $challenge,
            rpId: $this->rpId,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            timeout: 60000,
        );
        session(['passkey_challenge' => base64_encode($challenge)]);
        return response()->json($options);
    }

    public function connecter(Request $request): JsonResponse
    {
        $request->validate([
            'credential' => ['required', 'array'],
        ]);
        $challenge = base64_decode(session('passkey_challenge_auth'));

        if (!$challenge) {
            return response()->json(['error' => 'Challenge expiré.'], 422);
        }

        try {
            $credential = $request->input('credential');
            $credentialId = $credential['id'];
            $passkey = Passkey::where('credential_id', $credentialId)->first();

            if (!$passkey) {
                return response()->json(['error' => 'Passkey non reconnu.'], 422);
            }
            $clientDataJSON = base64_decode($credential['response']['clientDataJSON']);
            $clientData = json_decode($clientDataJSON, true);
            $receivedChallenge = base64_decode(strtr($clientData['challenge'], '-_', '+/'));

            if (!hash_equals($challenge, $receivedChallenge)) {
                return response()->json(['error' => 'Challenge invalide.'], 422);
            }

            if ($clientData['origin'] !== $this->origin) {
                return response()->json(['error' => 'Origine invalide.'], 422);
            }

            $passkey->update([
                'compteur' => $passkey->compteur + 1,
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
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur : ' . $e->getMessage()], 422);
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

    private function parseAttestationObject(string $attestationObject): array
    {
        return [
            'fmt' => 'none',
            'publicKey' => base64_encode($attestationObject),
            'signCount' => 0,
            'alg' => -7,
        ];
    }
}
