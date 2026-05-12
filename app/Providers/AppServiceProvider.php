<?php

namespace App\Providers;

use Laravel\Cashier\Events\WebhookReceived;
use App\Listeners\StripeEventListener;
use Illuminate\Support\Facades\Event;
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
    }
}
