<?php

namespace App\Services\Coffre;

use App\Models\Coffre;
use App\Models\ElementCoffre;
use App\Models\User;
use App\Services\Crypto\Contracts\EncryptionServiceInterface;

readonly class CoffreService
{
    public function __construct(
        private EncryptionServiceInterface $encryption,
        private CleManagementService $cleManagement,
    ) {}

    /**
     * @throws \SodiumException
     */
    public function creerCoffre(User $user, array $donnees, string $kek): Coffre
    {
        [
            'data_key' => $dataKey,
            'data_key_encrypted' => $dataKeyChiffree,
        ] = $this->cleManagement->genererDataKeyCoffre($kek);

        $coffre = Coffre::create([
            'user_id' => $user->id,
            'nom' => $donnees['nom'],
            'couleur' => $donnees['couleur'] ?? '#6366f1',
            'icone' => $donnees['icone'] ?? 'heroicon-o-lock-closed',
            'data_key_encrypted' => $dataKeyChiffree,
        ]);
        sodium_memzero($dataKey);

        return $coffre;
    }

    public function ajouterElement(
        Coffre $coffre,
        array $donnees,
        string $dataKey
    ): ElementCoffre {
        $payload = $this->construirePayload($donnees);
        $chiffre = $this->encryption->encrypt(json_encode($payload), $dataKey);

        return ElementCoffre::create([
            'coffre_id' => $coffre->id,
            'type' => $donnees['type'] ?? 'login',
            'label' => $donnees['label'],
            'url' => $donnees['url'] ?? null,
            'favicon_url' => $donnees['favicon_url'] ?? null,
            'payload_encrypted' => $chiffre['ciphertext'],
            'iv' => $chiffre['iv'],
            'auth_tag' => $chiffre['tag'],
            'version_schema' => 1,
            'favori' => false,
        ]);
    }

    public function listerElements(Coffre $coffre, string $dataKey): array
    {
        return $coffre->elements()
            ->whereNull('deleted_at')
            ->orderBy('label')
            ->get()
            ->map(fn(ElementCoffre $element) =>
            $this->dechiffrerElement($element, $dataKey)) ->toArray();
    }

    public function lireElement(ElementCoffre $element, string $dataKey): array
    {
        return $this->dechiffrerElement($element, $dataKey);
    }

    public function mettreAJourElement(
        ElementCoffre $element,
        array $donnees,
        string $dataKey
    ) : ElementCoffre {
        $ancienPayload = json_decode($this->encryption->decrypt(
            $element->payload_encrypted,
            $dataKey,
            $element->iv,
            $element->auth_tag,
        ), true) ?? [];

        $donneesMergees = $donnees;
        foreach ($ancienPayload as $cle => $valeur) {
            if (!isset($donneesMergees[$cle]) || $donneesMergees[$cle] === null || $donneesMergees[$cle] === '') {
                $donneesMergees[$cle] = $valeur;
            }
        }

        $payload = $this->construirePayload($donneesMergees);
        $chiffre = $this->encryption->encrypt(json_encode($payload), $dataKey);

        \Log::info('ancien payload', $ancienPayload);
        \Log::info('donnees mergees', $donneesMergees);
        $element->update([
            'label' => $donnees['label'],
            'url' => $donnees['url'] ?? $element->url,
            'favicon_url' => $donnees['favicon_url'] ?? $element->favicon_url,
            'payload_encrypted' => $chiffre['ciphertext'],
            'iv' => $chiffre['iv'],
            'auth_tag' => $chiffre['tag'],
        ]);

        return $element->fresh();
    }

    public function toggleFavori(ElementCoffre $element): bool
    {
        $element->update(['favori' => !$element->favori]);
        return $element->favori;
    }

    public function supprimerElement(ElementCoffre $element): void
    {
        $element->delete();
    }

    private function supprimerDefinitvement(ElementCoffre $element): void
    {
        $element->forceDelete();
    }

    public function resoudreFavicon(string $url): string
    {
        if (empty($url)) {
            return '';
        }

        $domain = parse_url($url, PHP_URL_HOST);

        if (!$domain) {
            return '';
        }

        return "https://www.google.com/s2/favicons?domain={$domain}&sz=128";
    }

    public function dechiffrerElement(ElementCoffre $element, string $dataKey): array
    {
        $crypto = $element->donneesChiffrement();
        $payloadJson = $this->encryption->decrypt(
            $crypto['payload'],
            $dataKey,
            $crypto['iv'],
            $crypto['tag']
        );

        return [
            'id' => $element->id,
            'type' => $element->type,
            'label' => $element->label,
            'url' => $element->url,
            'favicon_url' => $element->favicon_url,
            'favori' => $element->favori,
            'donnees' => json_decode($payloadJson, true),
            'created_at' => $element->created_at->toISOString(),
            'updated_at' => $element->updated_at->toISOString(),
        ];
    }

    private function construirePayload(array $donnees): array
    {
        $type = $donnees['type'] ?? 'login';

        $payload = match ($type) {
            'login' => [
                'identifiant' => $donnees['identifiant'] ?? '',
                'mot_de_passe' => $donnees['mot_de_passe'] ?? '',
                'totp_secret' => $donnees['totp_secret'] ?? null,
                'notes' => $donnees['notes'] ?? null,
                'champs_personnels' => $donnees['champs_personnels'] ?? [],
            ],

            'carte' => [
                'numero' => $donnees['numero'] ?? '',
                'titulaire' => $donnees['titulaire'] ?? '',
                'expiration' => $donnees['expiration'] ?? '',
                'cvv' => $donnees['cvv'] ?? '',
                'code_pin' => $donnees['code_pin'] ?? null,
                'notes' => $donnees['notes'] ?? null,
            ],

            'note' => [
                'contenu' => $donnees['contenu'] ?? '',
            ],

            'identite' => [
                'prenom' => $donnees['prenom'] ?? '',
                'nom' => $donnees['nom'] ?? '',
                'email' => $donnees['email'] ?? null,
                'telephone' => $donnees['telephone'] ?? null,
                'adresse' => $donnees['adresse'] ?? null,
                'passeport' => $donnees['passeport'] ?? null,
                'notes' => $donnees['notes'] ?? null,
            ],

            'cle_ssh' => [
                'serveur' => $donnees['serveur'] ?? '',
                'port' => $donnees['port'] ?? 22,
                'username' => $donnees['username'] ?? '',
                'cle_privee' => $donnees['cle_privee'] ?? null,
                'mot_de_passe' => $donnees['passphrase_ssh'] ?? null,
                'notes' => $donnees['notes'] ?? null,
            ],

            default => [
                'contenu' => $donnees['contenu'] ?? '',
                'notes' => $donnees['notes'] ?? null,
            ],
        };

        return array_filter($payload, fn($v) => $v !== null);
    }

}
