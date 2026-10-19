<?php

namespace Tests\Feature\Audit;

use App\Helpers\SessionHelper;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Env;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Tests\Support\LegacySchemaAuditTestCase;

final class GoogleOAuthFlowTest extends LegacySchemaAuditTestCase
{
    public function test_default_session_cookie_allows_oauth_return_without_disabling_cookie_protections(): void
    {
        $environment = Env::getRepository();
        $previous = $environment->get('SESSION_SAME_SITE');
        $environment->clear('SESSION_SAME_SITE');
        try {
            $session = require config_path('session.php');
        } finally {
            $previous === null ? $environment->clear('SESSION_SAME_SITE') : $environment->set('SESSION_SAME_SITE', $previous);
        }
        self::assertSame('lax', $session['same_site']);
        config(['session.same_site' => $session['same_site'], 'session.secure' => true, 'session.http_only' => true]);
        $this->google('cookie@example.test', 'cookie-subject');
        $response = $this->get('https://localhost/auth/google/redirect')->assertRedirect();
        $cookies = collect($response->headers->getCookies());
        $cookie = $cookies->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));
        self::assertNotNull($cookie);
        self::assertSame('lax', $cookie->getSameSite());
        self::assertTrue($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
    }

    public function test_example_environment_does_not_restore_the_strict_cookie_bug(): void
    {
        self::assertMatchesRegularExpression('/^SESSION_SAME_SITE=lax$/m', file_get_contents(base_path('.env.example')));
    }

    private function google(string $email, string $subject): void
    {
        $handler = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'access_token' => 'synthetic-token-only', 'token_type' => 'Bearer', 'expires_in' => 3600,
            ])),
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'sub' => $subject, 'email' => $email, 'email_verified' => true,
                'name' => 'Synthetic Google Account',
            ])),
        ]);
        // The installed Google provider runs normally; only its HTTP transport is synthetic.
        Socialite::extend('google', function ($app) use ($handler) {
            return (new GoogleProvider($app['request'], 'synthetic-client', 'synthetic-secret', route('auth.google.callback')))
                ->setHttpClient(new Client(['handler' => HandlerStack::create($handler)]));
        });
    }

    private function beginGoogle(): string
    {
        $response = $this->get('/auth/google/redirect')->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        self::assertSame('accounts.google.com', parse_url($response->headers->get('Location'), PHP_URL_HOST));
        self::assertNotEmpty($query['state']);
        $response->assertSessionHas('state', $query['state']);
        // Production has a fresh request/driver on callback; mirror that here.
        Socialite::forgetDrivers();

        return $query['state'];
    }

    public function test_new_google_account_sets_only_master_password_then_reaches_dashboard(): void
    {
        $this->google('new-google@example.test', 'synthetic-new-subject');
        $state = $this->beginGoogle();
        $this->get('/auth/google/callback?'.http_build_query(['code' => 'synthetic-code', 'state' => $state]))
            ->assertRedirect(route('oauth.master-password'))->assertSessionHas('oauth_new_user', true);
        $user = User::where('email', 'new-google@example.test')->sole();
        $this->assertAuthenticatedAs($user);
        self::assertTrue($user->hasVerifiedEmail());
        $this->get('/oauth/master-password')->assertOk()->assertSee('Créer votre Master Password')
            ->assertSee('name="master_password_confirmation"', false)
            ->assertDontSee('name="email"', false)->assertDontSee('name="password"', false);
        $this->post('/oauth/master-password', ['master_password' => self::MASTER, 'master_password_confirmation' => self::MASTER])
            ->assertRedirect(route('dashboard'))->assertSessionHasNoErrors();
        self::assertNotNull(SessionHelper::obtenirKek());
        self::assertSame(1, $user->coffres()->count());
        $this->get('/dashboard')->assertOk();
    }

    public function test_linked_google_account_unlocks_with_only_the_existing_master_password(): void
    {
        [$user] = $this->registeredUser(['oauth_provider' => 'google', 'oauth_id' => 'synthetic-existing-subject']);
        $this->google($user->email, 'synthetic-existing-subject');
        $state = $this->beginGoogle();
        $this->get('/auth/google/callback?'.http_build_query(['code' => 'synthetic-code', 'state' => $state]))
            ->assertRedirect(route('oauth.master-password'))->assertSessionHas('oauth_login', true);
        $this->get('/oauth/master-password')->assertOk()->assertSee('Déverrouiller le coffre')
            ->assertDontSee('name="master_password_confirmation"', false)
            ->assertDontSee('name="email"', false)->assertDontSee('name="password"', false);
        $this->post('/oauth/master-password', ['master_password' => self::MASTER])
            ->assertRedirect(route('dashboard'))->assertSessionHasNoErrors();
        $this->get('/dashboard')->assertOk();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_lost_session_on_google_return_reproduces_the_email_then_two_passwords_form(): void
    {
        $this->google('new-google@example.test', 'synthetic-new-subject');
        $state = $this->beginGoogle();
        // A browser omitting its session cookie arrives with no stored OAuth state.
        session()->flush();
        $this->get('/auth/google/callback?'.http_build_query(['code' => 'synthetic-code', 'state' => $state]))
            ->assertRedirect(route('connexion'));
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->get('/connexion')->assertSee('name="email"', false)->assertSee('Authentification OAuth échouée. Réessayez.');
        $this->post('/connexion', ['email' => 'new-google@example.test'])->assertRedirect(route('connexion.password'));
        $this->get('/connexion/password')->assertOk()->assertSee('name="password"', false)->assertSee('name="master_password"', false);
    }

    public function test_an_unlinked_local_account_is_a_different_rejection_case(): void
    {
        [$user] = $this->registeredUser();
        $this->google($user->email, 'synthetic-unlinked-subject');
        $state = $this->beginGoogle();
        $this->get('/auth/google/callback?'.http_build_query(['code' => 'synthetic-code', 'state' => $state]))
            ->assertRedirect(route('connexion'))->assertSessionHasErrors(['email' => 'Un compte existe déjà avec cette adresse. Connectez-vous avec ses identifiants avant de lier ce fournisseur.']);
        $this->assertGuest();
        self::assertNull($user->fresh()->oauth_provider);
    }

    public function test_tampered_oauth_state_does_not_create_or_authenticate_an_account(): void
    {
        $this->google('state@example.test', 'state-subject');
        $state = $this->beginGoogle();
        $this->get('/auth/google/callback?'.http_build_query(['code' => 'synthetic-code', 'state' => $state.'tampered']))
            ->assertRedirect(route('connexion'))->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        self::assertNull(SessionHelper::obtenirKek());
    }

    public function test_new_google_account_requires_matching_master_password_confirmation(): void
    {
        $this->google('confirmation@example.test', 'confirmation-subject');
        $state = $this->beginGoogle();
        $this->get('/auth/google/callback?'.http_build_query(['code' => 'synthetic-code', 'state' => $state]))
            ->assertRedirect(route('oauth.master-password'));
        $this->post('/oauth/master-password', ['master_password' => self::MASTER, 'master_password_confirmation' => 'different-password'])
            ->assertSessionHasErrors('master_password_confirmation');
        $this->assertDatabaseCount('cles_user', 0);
        $this->assertDatabaseCount('coffres', 0);
        self::assertNull(SessionHelper::obtenirKek());
    }

    public function test_google_authentication_alone_or_a_wrong_master_password_cannot_open_the_vault(): void
    {
        [$user] = $this->registeredUser(['oauth_provider' => 'google', 'oauth_id' => 'locked-subject']);
        $this->google($user->email, 'locked-subject');
        $state = $this->beginGoogle();
        $this->get('/auth/google/callback?'.http_build_query(['code' => 'synthetic-code', 'state' => $state]))
            ->assertRedirect(route('oauth.master-password'));
        $this->get('/dashboard')->assertRedirect(route('oauth.master-password'));
        $this->post('/oauth/master-password', ['master_password' => 'Wrong-Master-Password!'])
            ->assertSessionHasErrors('master_password');
        self::assertNull(SessionHelper::obtenirKek());
        $this->get('/dashboard')->assertRedirect(route('oauth.master-password'));
    }
}
