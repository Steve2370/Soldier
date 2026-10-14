<?php

use App\Http\Middleware\HasFamilySubscription;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\VaultUnlocked;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->redirectGuestsTo(fn () => route('connexion'));
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'vault.unlocked' => VaultUnlocked::class,
            'admin' => IsAdmin::class,
            'famille' => HasFamilySubscription::class,
        ]);
        $middleware->trimStrings(except: [
            'password',
            'mot_de_passe',
            'passphrase_ssh',
            'cle_privee',
            'master_password',
            'ancien_master_password',
            'nouveau_master_password',
            'nouveau_master_password_confirmation',
            'ancien_mot_de_passe',
            'nouveau_mot_de_passe',
            'nouveau_mot_de_passe_confirmation',
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash([
            'password', 'password_confirmation', 'current_password', 'ancien_mot_de_passe',
            'nouveau_mot_de_passe', 'nouveau_mot_de_passe_confirmation',
            'master_password', 'master_password_confirmation', 'ancien_master_password',
            'nouveau_master_password', 'nouveau_master_password_confirmation',
            'mot_de_passe', 'passphrase_ssh', 'cle_privee', 'totp_secret', 'code',
            'numero', 'cvv', 'code_pin', 'contenu', 'notes', 'champs_personnels',
        ]);
        $exceptions->render(function (HttpException $e, $request) {
            if ($e->getStatusCode() === 403 && ! $request->expectsJson()
                && ! $request->is('api/*', 'stripe/webhook')) {
                return redirect()->back()->with('toast', [
                    'type' => 'error',
                    'titre' => 'Accès refusé',
                    'message' => $e->getMessage() ?: 'Vous n\'avez pas les permissions nécessaires.',
                ]);
            }
        });
    })->create();
