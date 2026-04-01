<?php

use App\Http\Middleware\VaultUnlocked;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('connexion'));
        $middleware->alias([
            'vault.unlocked' => VaultUnlocked::class,
        ]);
        $middleware->trimStrings(except:[
            'password',
            'mot_de_passe',
            'master_password',
            'ancien_master_password',
            'nouveau_master_password',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
