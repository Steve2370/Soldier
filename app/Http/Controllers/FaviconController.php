<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Proxy/cache serveur pour les favicons (M3 de l'audit sécurité).
 *
 * Auparavant, le navigateur chargeait directement
 * https://www.google.com/s2/favicons?domain=... pour chaque élément du coffre —
 * ce qui révélait à Google la liste des domaines pour lesquels l'utilisateur a un
 * compte enregistré, à chaque affichage du tableau de bord. Un password manager
 * "zero-knowledge" ne devrait fuiter cette métadonnée à aucun tiers.
 *
 * Ce contrôleur fait la requête sortante vers Google lui-même, côté serveur (le
 * serveur connaît déjà le domaine en clair — il est stocké non chiffré dans
 * `elements_coffres.url`/`favicon_url` — donc rien n'est exposé de plus qu'avant à
 * l'opérateur du serveur), met le résultat en cache, et ne sert au navigateur que
 * des octets d'image provenant de notre propre domaine.
 */
class FaviconController extends Controller
{
    private const int CACHE_TTL_JOURS = 14;

    /**
     * Format de nom de domaine valide (labels alphanumériques séparés par des
     * points) — rejette toute valeur qui ne ressemble pas à un hostname avant de
     * la transmettre, même en tant que simple paramètre de requête, à Google.
     */
    private const string REGEX_DOMAINE = '/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)+$/i';

    public function afficher(Request $request): Response
    {
        $request->validate([
            'domain' => ['required', 'string', 'max:253', 'regex:' . self::REGEX_DOMAINE],
        ]);

        $domain = strtolower($request->query('domain'));
        $cleCache = 'favicon:' . $domain;

        $image = Cache::get($cleCache);

        if (!$image) {
            $image = $this->recuperer($domain);

            if ($image) {
                Cache::put($cleCache, $image, now()->addDays(self::CACHE_TTL_JOURS));
            }
        }

        if (!$image) {
            // Échec (timeout, domaine inconnu de Google...) : ne pas mettre en
            // cache un échec, pour ne pas rester bloqué dessus si Google était
            // simplement temporairement indisponible.
            return response('', 204);
        }

        return response(base64_decode($image['body']))
            ->header('Content-Type', $image['content_type'])
            ->header('Cache-Control', 'public, max-age=' . (60 * 60 * 24 * self::CACHE_TTL_JOURS));
    }

    /**
     * @return array{body: string, content_type: string}|null
     */
    private function recuperer(string $domain): ?array
    {
        try {
            $reponse = Http::timeout(5)->get('https://www.google.com/s2/favicons', [
                'domain' => $domain,
                'sz' => 128,
            ]);
        } catch (\Throwable) {
            return null;
        }

        if (!$reponse->successful()) {
            return null;
        }

        return [
            'body' => base64_encode($reponse->body()),
            'content_type' => $reponse->header('Content-Type') ?: 'image/png',
        ];
    }
}
