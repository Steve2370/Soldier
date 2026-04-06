<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GenerateurController;
use App\Http\Controllers\PartageController;
use App\Http\Controllers\PasskeyController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/welcome', [WelcomeController::class, 'index'])->name('welcome');

    Route::get('/connexion', [AuthController::class, 'showConnexion'])->name('connexion');
    Route::post('/connexion', [AuthController::class, 'verifierEmail'])->name('connexion.post');
    Route::get('/connexion/password', [AuthController::class, 'showPassword'])->name('connexion.password');
    Route::post('/connexion/password', [AuthController::class, 'connecter'])->name('connexion.password.post');

    Route::get('/inscription', [AuthController::class, 'showInscription'])->name('inscription');
    Route::post('/inscription', [AuthController::class, 'inscrire'])->name('inscription.post');
});

Route::get('/verification-mfa', [AuthController::class, 'showMfa'])->name('mfa.verify');
Route::post('/verification-mfa', [AuthController::class, 'verifierMfa'])->name('mfa.verify.post');

Route::get('/auth/github/redirect', [AuthController::class, 'redirectGithub'])->name('auth.github.redirect');
Route::get('/auth/github/callback', [AuthController::class, 'callbackGithub'])->name('auth.github.callback');
Route::get('/auth/google/redirect', [AuthController::class, 'redirectGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'callbackGoogle'])->name('auth.google.callback');

Route::middleware(['auth'])->group(function () {

    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/oauth/master-password', [AuthController::class, 'showOauthMasterPassword'])->name('oauth.master-password');
    Route::post('/oauth/master-password', [AuthController::class, 'configurerOauthMasterPassword'])->name('oauth.master-password.post');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/services/creer', [DashboardController::class, 'creer'])->name('services.creer');
    Route::post('/services', [DashboardController::class, 'stocker'])->name('services.stocker');
    Route::get('/services/{element}', [DashboardController::class, 'afficher'])->name('services.afficher');
    Route::get('/services/{element}/modifier', [DashboardController::class, 'modifier'])->name('services.modifier');
    Route::put('/services/{element}', [DashboardController::class, 'mettreAJour'])->name('services.mettreAJour');
    Route::delete('/services/{element}', [DashboardController::class, 'supprimer'])->name('services.supprimer');
    Route::patch('/services/{element}/favori', [DashboardController::class, 'toggleFavori'])->name('services.favori');

    Route::get('/generateur', [GenerateurController::class, 'index'])->name('generateur');

    Route::get('/partage', [PartageController::class, 'index'])->name('partage.index');
    Route::post('/partage', [PartageController::class, 'envoyer'])->name('partage.envoyer');
    Route::delete('/partage/{share}', [PartageController::class, 'revoquer'])->name('partage.revoquer');
    Route::patch('/partage/{invitation}/annuler', [PartageController::class, 'annulerInvitation'])->name('partage.annuler');

    Route::post('/passkeys/options-inscription', [PasskeyController::class, 'optionsInscription'])->name('passkeys.options-inscription');
    Route::post('/passkeys/inscrire', [PasskeyController::class, 'inscrire'])->name('passkeys.inscrire');
    Route::delete('/passkeys/{passkey}', [PasskeyController::class, 'supprimer'])->name('passkeys.supprimer');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/avatar', [SettingsController::class, 'changerAvatar'])->name('settings.avatar');
    Route::delete('/settings/avatar', [SettingsController::class, 'supprimerAvatar'])->name('settings.avatar.supprimer');
    Route::post('/settings/mfa/email/activer', [SettingsController::class, 'activerMfaEmail'])->name('settings.mfa.email.activer');
    Route::post('/settings/mfa/email/desactiver', [SettingsController::class, 'desactiverMfaEmail'])->name('settings.mfa.email.desactiver');
    Route::get('/settings/totp/configurer', [SettingsController::class, 'configurerTotp'])->name('settings.totp.configurer');
    Route::post('/settings/totp/valider', [SettingsController::class, 'validerTotp'])->name('settings.totp.valider');
    Route::post('/settings/totp/desactiver', [SettingsController::class, 'desactiverTotp'])->name('settings.totp.desactiver');
    Route::post('/settings/mot-de-passe', [SettingsController::class, 'changerMotDePasse'])->name('settings.mot-de-passe');
    Route::post('/settings/mot-de-passe/compte', [SettingsController::class, 'changerMotDePasseCompte'])->name('settings.mot-de-passe.compte');
    Route::get('/privacy', fn() => view('privacy'))->name('privacy');

    Route::post('/deconnexion', [AuthController::class, 'deconnexion'])->name('deconnexion');
});

Route::get('/invitation/{token}', [PartageController::class, 'accepter'])->name('partage.accepter');

Route::post('/passkeys/options-connexion', [PasskeyController::class, 'optionsConnexion'])->name('passkeys.options-connexion');
Route::post('/passkeys/connecter', [PasskeyController::class, 'connecter'])->name('passkeys.connecter');
