<?php

namespace App\Http\Middleware;

use App\Helpers\SessionHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VaultUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if (SessionHelper::obtenirMfaUserIdPending() !== null
            || ! SessionHelper::havecleKek() || ! SessionHelper::haveClePrivee()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'vault_locked',
                    'message' => 'Session expirée. Veuillez vous reconnecter.',
                ], 423);
            }

            return redirect()->route(SessionHelper::obtenirMfaUserIdPending() !== null ? 'mfa.verify' : 'oauth.master-password')
                ->with('toast', [
                    'type' => 'warning',
                    'titre' => 'Session expirée',
                    'message' => 'Veuillez saisir votre master password pour déverrouiller.',
                ]);
        }

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
