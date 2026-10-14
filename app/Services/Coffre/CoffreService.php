<?php

namespace App\Services\Coffre;

use App\Models\Coffre;
use App\Models\ElementCoffre;
use App\Models\User;
use App\Services\Coffre\Contracts\ElementPayloadInterface;
use App\Services\Crypto\Contracts\EncryptionServiceInterface;
use Illuminate\Validation\ValidationException;

readonly class CoffreService
{
    public function __construct(
        private EncryptionServiceInterface $encryption,
        private CleManagementService $cleManagement,
        private ElementPayloadInterface $payloads,
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
        $payload = $this->payloads->build($donnees['type'] ?? 'login', $donnees);
        $chiffre = $this->encryption->encrypt(json_encode($payload, JSON_THROW_ON_ERROR), $dataKey);

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

    public function listerElements(Coffre $coffre, string $dataKey, ?array $elementIds = null): array
    {
        $query = $coffre->elements()
            ->whereNull('deleted_at')
            ->orderBy('label');

        if ($elementIds !== null) {
            $query->whereIn('id', $elementIds);
        }

        return $query->get()
            ->map(fn (ElementCoffre $element) => $this->dechiffrerElement($element, $dataKey))
            ->toArray();
    }

    public function lireElement(ElementCoffre $element, string $dataKey): array
    {
        return $this->dechiffrerElement($element, $dataKey);
    }

    public function mettreAJourElement(
        ElementCoffre $element,
        array $donnees,
        string $dataKey
    ): ElementCoffre {
        $ancienPayload = json_decode($this->encryption->decrypt(
            $element->payload_encrypted,
            $dataKey,
            $element->iv,
            $element->auth_tag,
        ), true, 512, JSON_THROW_ON_ERROR);

        if (isset($donnees['type']) && $donnees['type'] !== $element->type) {
            throw ValidationException::withMessages(['type' => 'Le type d’un élément existant ne peut pas être changé.']);
        }
        $payload = $this->payloads->build($element->type, $donnees, $ancienPayload);
        $chiffre = $this->encryption->encrypt(json_encode($payload, JSON_THROW_ON_ERROR), $dataKey);

        $element->update([
            'label' => $donnees['label'],
            'url' => array_key_exists('url', $donnees) ? $donnees['url'] : $element->url,
            'favicon_url' => array_key_exists('favicon_url', $donnees) ? $donnees['favicon_url'] : $element->favicon_url,
            'payload_encrypted' => $chiffre['ciphertext'],
            'iv' => $chiffre['iv'],
            'auth_tag' => $chiffre['tag'],
        ]);

        return $element->fresh();
    }

    public function toggleFavori(ElementCoffre $element): bool
    {
        $element->update(['favori' => ! $element->favori]);

        return $element->favori;
    }

    public function supprimerElement(ElementCoffre $element): void
    {
        $element->delete();
    }

    public function supprimerDefinitivement(ElementCoffre $element): void
    {
        $element->forceDelete();
    }

    /**
     * Renvoie une URL vers NOTRE proxy de favicon (FaviconController), jamais
     * directement vers Google : sinon, chaque affichage d'un élément du coffre
     * révélerait à Google le domaine associé (fuite de métadonnées vers un tiers
     * — voir M3 de l'audit sécurité).
     */
    public function resoudreFavicon(string $url, string $label = ''): string
    {
        if (! empty($url)) {
            $domain = parse_url($url, PHP_URL_HOST);
            if ($domain) {
                return route('favicon', ['domain' => $domain]);
            }
        }

        if (! empty($label)) {
            $slug = strtolower(preg_replace('/\s+/', '', $label));

            return route('favicon', ['domain' => $slug.'.com']);
        }

        return '';
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
}
