<?php

namespace App\Services\Auth;

use App\Models\User;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Random\RandomException;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\Exception\WebauthnException;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * Encapsule les cérémonies WebAuthn (enregistrement et authentification par passkey)
 * en s'appuyant sur web-auth/webauthn-lib pour la vérification cryptographique réelle
 * (parsing CBOR de l'attestation, extraction de la clé publique COSE, vérification de
 * signature ECDSA/RSA, anti-rejeu du compteur). Le contrôleur ne fait plus aucune
 * vérification "maison" : il délègue entièrement à ce service (SRP).
 *
 * @throws WebauthnException en cas d'échec de vérification (challenge, origine, signature, compteur, etc.)
 */
readonly class PasskeyService
{
    private string $rpId;
    private string $rpName;
    private string $origin;
    private AttestationStatementSupportManager $attestationStatementSupportManager;

    public function __construct()
    {
        $this->rpId = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
        $this->rpName = config('app.name', 'Soldier');
        $this->origin = rtrim(config('app.url'), '/');
        $this->attestationStatementSupportManager = new AttestationStatementSupportManager([
            new NoneAttestationStatementSupport(),
        ]);
    }

    /**
     * @return array{challenge: string, json: string}
     * @throws RandomException
     */
    public function optionsInscription(User $user): array
    {
        $rpEntity = PublicKeyCredentialRpEntity::create(name: $this->rpName, id: $this->rpId);
        $userEntity = PublicKeyCredentialUserEntity::create(
            name: $user->email,
            id: (string) $user->id,
            displayName: $user->name,
        );

        $excludeCredentials = $user->passKeys()->get()->map(
            fn ($passkey) => PublicKeyCredentialDescriptor::create(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                Base64UrlSafe::decodeNoPadding($passkey->credential_id),
            )
        )->all();

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
            excludeCredentials: $excludeCredentials,
            timeout: 60000,
        );

        return [
            'challenge' => base64_encode($challenge),
            'json' => $this->serializer()->serialize($options, 'json'),
        ];
    }

    /**
     * Vérifie une réponse d'attestation (enregistrement) et retourne la source de clé
     * publique validée, prête à être persistée.
     *
     * @throws WebauthnException
     */
    public function verifierInscription(string $credentialJson, string $challengeAttendu, User $user): PublicKeyCredentialSource
    {
        $publicKeyCredential = $this->serializer()->deserialize($credentialJson, PublicKeyCredential::class, 'json');

        if (! $publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            throw new WebauthnException("La réponse n'est pas une réponse d'attestation.");
        }

        $rpEntity = PublicKeyCredentialRpEntity::create(name: $this->rpName, id: $this->rpId);
        $userEntity = PublicKeyCredentialUserEntity::create(
            name: $user->email,
            id: (string) $user->id,
            displayName: $user->name,
        );
        $creationOptions = PublicKeyCredentialCreationOptions::create(
            rp: $rpEntity,
            user: $userEntity,
            challenge: base64_decode($challengeAttendu),
            pubKeyCredParams: [
                PublicKeyCredentialParameters::create('public-key', -7),
                PublicKeyCredentialParameters::create('public-key', -257),
            ],
        );

        $ceremonyStepManagerFactory = new CeremonyStepManagerFactory();
        $ceremonyStepManagerFactory->setAttestationStatementSupportManager($this->attestationStatementSupportManager);
        $ceremonyStepManagerFactory->setAllowedOrigins([$this->origin]);

        $validator = AuthenticatorAttestationResponseValidator::create(
            $ceremonyStepManagerFactory->creationCeremony()
        );

        return $validator->check($publicKeyCredential->response, $creationOptions, $this->rpId);
    }

    /**
     * @return array{challenge: string, json: string}
     * @throws RandomException
     */
    public function optionsConnexion(): array
    {
        $challenge = random_bytes(32);
        $options = PublicKeyCredentialRequestOptions::create(
            challenge: $challenge,
            rpId: $this->rpId,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            timeout: 60000,
        );

        return [
            'challenge' => base64_encode($challenge),
            'json' => $this->serializer()->serialize($options, 'json'),
        ];
    }

    /**
     * Vérifie une réponse d'assertion (connexion) contre la source de clé publique
     * précédemment enregistrée, et retourne la source mise à jour (compteur anti-rejeu
     * incrémenté) à repersister par l'appelant.
     *
     * @throws WebauthnException
     */
    public function verifierConnexion(
        string $credentialJson,
        string $challengeAttendu,
        PublicKeyCredentialSource $sourceStockee,
    ): PublicKeyCredentialSource {
        $publicKeyCredential = $this->serializer()->deserialize($credentialJson, PublicKeyCredential::class, 'json');

        if (! $publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
            throw new WebauthnException("La réponse n'est pas une réponse d'assertion.");
        }

        $requestOptions = PublicKeyCredentialRequestOptions::create(
            challenge: base64_decode($challengeAttendu),
            rpId: $this->rpId,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED,
        );

        $ceremonyStepManagerFactory = new CeremonyStepManagerFactory();
        $ceremonyStepManagerFactory->setAllowedOrigins([$this->origin]);

        $validator = AuthenticatorAssertionResponseValidator::create(
            $ceremonyStepManagerFactory->requestCeremony()
        );

        // userHandle = null : flux "discoverable" (usernameless), le navigateur renvoie
        // lui-même le userHandle dans la réponse d'assertion, vérifié par la librairie
        // (CheckUserHandle) contre celui de la source stockée.
        return $validator->check(
            $sourceStockee,
            $publicKeyCredential->response,
            $requestOptions,
            $this->rpId,
            null,
        );
    }

    public function serialiserSource(PublicKeyCredentialSource $source): string
    {
        return $this->serializer()->serialize($source, 'json');
    }

    public function desserialiserSource(string $json): PublicKeyCredentialSource
    {
        return $this->serializer()->deserialize($json, PublicKeyCredentialSource::class, 'json');
    }

    /**
     * Encodage base64url (sans padding) de l'identifiant de credential, dans le même
     * format que la propriété `id` envoyée par le navigateur (spec WebAuthn) — c'est
     * cette chaîne qui est stockée dans la colonne `credential_id` pour permettre la
     * recherche directe lors de la connexion.
     */
    public function credentialIdString(PublicKeyCredentialSource $source): string
    {
        return Base64UrlSafe::encodeUnpadded($source->publicKeyCredentialId);
    }

    private function serializer(): SerializerInterface
    {
        return (new WebauthnSerializerFactory($this->attestationStatementSupportManager))->create();
    }
}
