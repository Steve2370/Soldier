<?php

namespace App\Providers;

use Laravel\Cashier\Events\WebhookReceived;
use App\Listeners\StripeEventListener;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(WebhookReceived::class, StripeEventListener::class);

        $this->registerRateLimiters();
    }

    /**
     * Limiteurs de débit pour les points d'entrée sensibles à l'énumération/brute-force :
     * mot de passe de connexion, inscription (anti-création de comptes en masse) et code
     * MFA (email/TOTP — sans cela, un code à 6 chiffres est devinable en un temps
     * raisonnable une fois une session pré-authentifiée obtenue).
     */
    private function registerRateLimiters(): void
    {
        RateLimiter::for('connexion', function (Request $request) {
            // L'email est dans le corps de la requête à l'étape 1 (/connexion) et
            // uniquement en session à l'étape 2 (/connexion/password).
            $email = $request->input('email') ?? session('login_email');
            $cle = strtolower((string) $email) . '|' . $request->ip();

            return Limit::perMinute(5)->by($cle);
        });

        RateLimiter::for('inscription', function (Request $request) {
            return Limit::perHour(5)->by($request->ip());
        });

        RateLimiter::for('mfa', function (Request $request) {
            $cle = ($request->user()?->id ?? 'invite') . '|' . $request->ip();

            return Limit::perMinute(5)->by($cle);
        });
    }
}
