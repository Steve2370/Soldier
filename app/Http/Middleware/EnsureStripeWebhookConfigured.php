<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureStripeWebhookConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cashier's signature middleware is optional when the secret is absent.
        // Fail closed instead of accepting unauthenticated subscription changes.
        abort_unless(is_string(config('cashier.webhook.secret')) && trim(config('cashier.webhook.secret')) !== '', 503);

        return $next($request);
    }
}
