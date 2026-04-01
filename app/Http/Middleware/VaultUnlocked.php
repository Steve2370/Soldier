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
        if (!SessionHelper::havecleKek()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'vault_locked',
                    'message' => 'Session expirée. Veuillez vous reconnecter.',
                ], 423);
            }

            return redirect()->route('connexion')
                ->with('toast', [
                    'type' => 'warning',
                    'titre' => 'Session expirée',
                    'message' => 'Veuillez saisir votre master password pour déverrouiller.',
                ]);
        }

        return $next($request);
    }
}
