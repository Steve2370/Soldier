<?php

namespace Tests\Feature\Audit;

use App\Models\Passkey;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacySchemaAuditTestCase;

class PasskeysAndInfrastructureTest extends LegacySchemaAuditTestCase
{
    public function test_passkey_login_options_have_random_challenges_and_require_verification(): void
    {
        $first = $this->postJson('/passkeys/options-connexion')->assertOk();
        $second = $this->postJson('/passkeys/options-connexion')->assertOk();
        self::assertNotSame($first->json('challenge'), $second->json('challenge'));
        self::assertSame('required', $second->json('userVerification'));
        self::assertSame('localhost', $second->json('rpId'));
    }

    public function test_passkey_registration_options_include_user_and_excluded_credentials(): void
    {
        $user = $this->user();
        $this->unlock($user, random_bytes(32));
        $credential = rtrim(strtr(base64_encode('audit-credential-id'), '+/', '-_'), '=');
        Passkey::create(['user_id' => $user->id, 'nom' => 'Audit', 'credential_id' => $credential, 'cle_publique' => '{}']);
        $response = $this->postJson('/passkeys/options-inscription')->assertOk();
        self::assertSame($user->email, $response->json('user.name'));
        self::assertSame('required', $response->json('authenticatorSelection.userVerification'));
        self::assertSame($credential, $response->json('excludeCredentials.0.id'));
    }

    public function test_passkey_registration_without_challenge_is_rejected(): void
    {
        $this->unlock($this->user(), random_bytes(32));
        $this->postJson('/passkeys/inscrire', ['credential' => ['id' => 'audit']])->assertUnprocessable();
        $this->assertDatabaseCount('passkeys', 0);
    }

    public function test_malformed_passkey_attestation_cannot_be_registered(): void
    {
        $this->unlock($this->user(), random_bytes(32));
        $this->postJson('/passkeys/options-inscription')->assertOk();
        $this->postJson('/passkeys/inscrire', ['credential' => ['id' => 'audit', 'type' => 'public-key']])->assertUnprocessable();
        $this->assertDatabaseCount('passkeys', 0);
    }

    public function test_unknown_passkey_cannot_authenticate(): void
    {
        $this->postJson('/passkeys/options-connexion')->assertOk();
        $this->postJson('/passkeys/connecter', ['credential' => ['id' => 'unknown']])->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_passkey_deletion_is_owner_scoped(): void
    {
        $owner = $this->user();
        $key = Passkey::create(['user_id' => $owner->id, 'nom' => 'Audit', 'credential_id' => 'audit', 'cle_publique' => '{}']);
        $this->unlock($this->user(), random_bytes(32));
        $this->delete('/passkeys/'.$key->id);
        $this->assertDatabaseHas('passkeys', ['id' => $key->id]);
        $this->unlock($owner, random_bytes(32));
        $this->delete('/passkeys/'.$key->id)->assertRedirect();
        $this->assertDatabaseMissing('passkeys', ['id' => $key->id]);
    }

    public function test_signed_email_verification_marks_the_correct_user(): void
    {
        $user = $this->user(['email_verified_at' => null]);
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(10), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);
        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard'));
        self::assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_expired_email_verification_does_not_verify_account(): void
    {
        $user = $this->user(['email_verified_at' => null]);
        $url = URL::temporarySignedRoute('verification.verify', now()->subMinute(), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);
        $this->actingAs($user)->get($url);
        self::assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_favicon_proxy_is_cached_and_does_not_fetch_user_supplied_urls(): void
    {
        $this->unlock($this->user(), random_bytes(32));
        Http::fake(['www.google.com/*' => Http::response('fake-image', 200, ['Content-Type' => 'image/png'])]);
        $this->get('/favicon?domain=example.com')->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->get('/favicon?domain=EXAMPLE.COM')->assertOk();
        Http::assertSentCount(1);
        $this->getJson('/favicon?domain=http://localhost/private')->assertUnprocessable();
        Http::assertSentCount(1);
    }

    public function test_favicon_outage_returns_empty_response(): void
    {
        $this->unlock($this->user(), random_bytes(32));
        Http::fake(['*' => Http::response('', 503)]);
        $this->get('/favicon?domain=example.com')->assertNoContent();
    }

    public function test_https_response_adds_hsts(): void
    {
        $this->get('https://localhost/')->assertOk()->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_csrf_rejects_state_change_without_token_outside_test_bypass(): void
    {
        $this->app['env'] = 'local';
        $this->post('/connexion', ['email' => 'csrf@example.test'])->assertStatus(419);
    }

    public static function mailClasses(): array
    {
        return array_map(fn ($name) => ['App\\Mail\\'.$name], [
            'AdminMailMasse', 'BienvenueMail', 'CodeMfaMail', 'InvitationAccepteeMail',
            'InvitationPartageMail', 'MembreFamilleAjouteMail', 'MembreInviteFamilleMail',
            'MotDePasseChangeMail', 'NouvelleConnexionMail', 'PartageGroupeFamilleMail',
            'TotpActiveMail', 'TotpDesactiveMail',
        ]);
    }

    #[DataProvider('mailClasses')]
    public function test_email_template_renders_with_escaped_user_content(string $class): void
    {
        $user = $this->user(['name' => '<script>auditMarker()</script>']);
        $reflection = new \ReflectionClass($class);
        $arguments = array_map(function (\ReflectionParameter $parameter) use ($user) {
            return $parameter->getType()?->getName() === User::class
                ? $user
                : (str_contains(strtolower($parameter->name), 'lien') ? 'https://example.test/invitation/audit' : 'Audit content');
        }, $reflection->getConstructor()->getParameters());
        $html = $reflection->newInstanceArgs($arguments)->render();
        self::assertNotEmpty($html);
        self::assertStringNotContainsString('<script>auditMarker()</script>', $html);
    }
}
