<?php

namespace Tests\Feature\Audit;

use App\Helpers\SessionHelper;
use App\Mail\CodeMfaMail;
use App\Models\User;
use App\Services\Auth\MfaService;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as OAuthUser;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacySchemaAuditTestCase;

class AuthenticationTest extends LegacySchemaAuditTestCase
{
    public function test_password_login_unlocks_the_vault(): void
    {
        [$user, $keys] = $this->registeredUser();
        $this->withSession(['login_email' => $user->email])->post('/connexion/password', [
            'password' => self::PASSWORD, 'master_password' => self::MASTER,
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        self::assertSame($keys['kek'], SessionHelper::obtenirKek());
    }

    public static function wrongCredentials(): array
    {
        return [['password'], ['master_password']];
    }

    #[DataProvider('wrongCredentials')]
    public function test_wrong_credential_leaves_user_logged_out(string $field): void
    {
        [$user] = $this->registeredUser();
        $this->withSession(['login_email' => $user->email])->post('/connexion/password', array_merge([
            'password' => self::PASSWORD, 'master_password' => self::MASTER,
        ], [$field => 'incorrect-value']))->assertSessionHasErrors($field);
        $this->assertGuest();
        self::assertNull(SessionHelper::obtenirKek());
    }

    public function test_new_account_can_log_in_with_an_uppercase_email(): void
    {
        [$user] = $this->registeredUser(['email' => 'MixedCase@Example.test']);
        $this->post('/connexion', ['email' => $user->email])->assertRedirect(route('connexion.password'));
        $this->post('/connexion/password', [
            'password' => self::PASSWORD, 'master_password' => self::MASTER,
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_notification_outage_does_not_break_successful_login(): void
    {
        [$user] = $this->registeredUser();
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Synthetic provider outage'));
        $this->withSession(['login_email' => $user->email])->post('/connexion/password', [
            'password' => self::PASSWORD, 'master_password' => self::MASTER,
        ])->assertRedirect(route('dashboard'));
    }

    public function test_email_mfa_requires_valid_code_and_consumes_it(): void
    {
        [$user] = $this->registeredUser();
        $user->mfa()->create(['type' => 'email', 'actif' => true]);
        $this->withSession(['login_email' => $user->email])->post('/connexion/password', [
            'password' => self::PASSWORD, 'master_password' => self::MASTER,
        ])->assertRedirect(route('mfa.verify'));
        $this->assertGuest();
        self::assertNull(SessionHelper::obtenirKek());
        $code = Mail::sent(CodeMfaMail::class)->sole()->code;
        $this->post('/verification-mfa', ['code' => $code])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        self::assertTrue(SessionHelper::mfaVerifie());
        self::assertNull($user->mfa()->first()->code_hash);
        self::assertNull(SessionHelper::obtenirMfaPendante());
    }

    public function test_oauth_unlock_sends_the_email_mfa_challenge(): void
    {
        [$user] = $this->registeredUser(['oauth_provider' => 'google', 'oauth_id' => 'audit-google']);
        $user->mfa()->create(['type' => 'email', 'actif' => true]);
        $this->actingAs($user)->withSession(['oauth_login' => true])->post('/oauth/master-password', [
            'master_password' => self::MASTER,
        ])->assertRedirect(route('mfa.verify'));
        Mail::assertSent(CodeMfaMail::class);
        self::assertNotNull($user->mfa()->first()->code_hash);
    }

    public function test_recovery_code_is_accepted_by_the_mfa_form(): void
    {
        [$user, $keys] = $this->registeredUser();
        $user->mfa()->create(['type' => 'totp', 'actif' => true]);
        $codes = app(MfaService::class)->genererCodesRecuperation($user);
        SessionHelper::stockerMfaPendante($keys['kek'], $keys['cle_privee'], 'totp');
        SessionHelper::mfaUserIdPending($user->id);
        $this->post('/verification-mfa', ['code' => $codes[0]])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_mfa_notification_failure_cannot_leave_an_authenticated_session(): void
    {
        [$user] = $this->registeredUser();
        $user->mfa()->create(['type' => 'email', 'actif' => true]);
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Synthetic provider outage'));
        $this->withSession(['login_email' => $user->email])->post('/connexion/password', [
            'password' => self::PASSWORD, 'master_password' => self::MASTER,
        ]);
        $this->assertGuest();
    }

    public function test_google_requires_positive_email_verification(): void
    {
        $oauth = (new OAuthUser)->setRaw([])->map([
            'id' => 'google-audit-id', 'email' => 'unverified@example.test', 'name' => 'Unverified',
        ]);
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($oauth);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        $this->get('/auth/google/callback')->assertRedirect(route('connexion'));
        $this->assertGuest();
        self::assertFalse(User::where('email', 'unverified@example.test')->exists());
    }

    public function test_google_does_not_link_an_existing_local_account(): void
    {
        $user = $this->user();
        $oauth = (new OAuthUser)->setRaw(['email_verified' => true])->map([
            'id' => 'google-existing-id', 'email' => $user->email, 'name' => 'Existing',
        ]);
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($oauth);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        $this->get('/auth/google/callback')->assertRedirect(route('connexion'))->assertSessionHasErrors('email');
        self::assertNull($user->fresh()->oauth_provider);
        $this->assertGuest();
    }

    public function test_google_provider_failure_returns_a_handled_error(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andThrow(new \RuntimeException('Synthetic OAuth outage'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        $this->get('/auth/google/callback')->assertRedirect(route('connexion'))->assertSessionHasErrors('email');
    }

    public function test_login_rate_limit_blocks_the_sixth_attempt(): void
    {
        $this->withSession(['login_email' => 'unknown@example.test']);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/connexion/password', ['password' => 'wrong', 'master_password' => 'wrong'])->assertRedirect();
        }
        $this->post('/connexion/password', ['password' => 'wrong', 'master_password' => 'wrong'])->assertStatus(429);
    }

    public function test_logout_removes_all_key_material_and_mfa_state(): void
    {
        $this->unlock($this->user(), random_bytes(32));
        SessionHelper::marquerMfaVerifie();
        SessionHelper::stockerMfaPendante(random_bytes(32), 'pending-key', 'email');
        $this->post('/deconnexion')->assertRedirect(route('connexion'));
        $this->assertGuest();
        self::assertNull(SessionHelper::obtenirKek());
        self::assertNull(SessionHelper::obtenirClePrivee());
        self::assertNull(SessionHelper::obtenirMfaPendante());
        self::assertFalse(SessionHelper::mfaVerifie());
    }
}
