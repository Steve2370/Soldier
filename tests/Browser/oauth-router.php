<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Two\GoogleProvider;

// Test-only entry point: never install this router in a web server configuration.
$directory = getenv('SOLDIER_E2E_DIRECTORY');
$providerOrigin = getenv('SOLDIER_E2E_PROVIDER_ORIGIN');
if (! is_string($directory) || ! preg_match('#^/.*soldier-e2e-[^/]+$#', $directory)
    || ! is_dir($directory) || getenv('DB_DATABASE') !== $directory.'/database.sqlite'
    || getenv('DB_CONNECTION') !== 'sqlite' || getenv('MAIL_MAILER') !== 'array'
    || getenv('APP_ENV') !== 'local' || getenv('DB_URL') !== ''
    || ! is_string($providerOrigin) || ! preg_match('#^http://127\.0\.0\.1:[0-9]+$#', $providerOrigin)
    || ! preg_match('#^http://localhost:[0-9]+$#', (string) getenv('APP_URL'))) {
    throw new RuntimeException('Refusing to simulate OAuth outside an isolated local browser test.');
}

$root = dirname(__DIR__, 2);
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = realpath($root.'/public'.$path);
if ($path !== '/' && $file && str_starts_with($file, $root.'/public/') && is_file($file)) {
    return false;
}

require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->booted(function () use ($app): void {
    $app->make(Factory::class)->extend('google', function ($app) {
        $provider = new class($app['request'], 'browser-test-client', 'browser-test-secret', getenv('APP_URL').'/auth/google/callback') extends GoogleProvider
        {
            protected function getAuthUrl($state)
            {
                return $this->buildAuthUrlFromBase(getenv('SOLDIER_E2E_PROVIDER_ORIGIN').'/google', $state);
            }
        };
        // Keep Google's actual profile mapping and OAuth state verification. Only the
        // authorization screen and external HTTP transport are replaced with test data.
        $handler = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'access_token' => 'synthetic-browser-token', 'token_type' => 'Bearer', 'expires_in' => 3600,
            ], JSON_THROW_ON_ERROR)),
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'sub' => 'browser-google-subject', 'email' => 'google-browser@example.test',
                'email_verified' => true, 'name' => 'Browser Google Account',
            ], JSON_THROW_ON_ERROR)),
        ]);

        return $provider->setHttpClient(new Client(['handler' => HandlerStack::create($handler)]));
    });
});
$app->handleRequest(Request::capture());
