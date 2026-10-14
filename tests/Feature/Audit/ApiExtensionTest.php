<?php

namespace Tests\Feature\Audit;

use App\Services\Auth\ExtensionHandoffService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacySchemaAuditTestCase;

class ApiExtensionTest extends LegacySchemaAuditTestCase
{
    public function test_api_password_login_and_logout(): void
    {
        [$user] = $this->registeredUser();
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email, 'password' => self::PASSWORD, 'master_password' => self::MASTER,
        ])->assertOk()->assertJsonStructure(['token', 'kdf', 'coffre', 'cle_privee_chiffree']);
        self::assertSame(['read:services'], $user->tokens()->sole()->abilities);
        $this->withToken($response->json('token'))->postJson('/api/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_api_login_requires_verified_email(): void
    {
        [$user] = $this->registeredUser(['email_verified_at' => null]);
        $this->postJson('/api/auth/login', [
            'email' => $user->email, 'password' => self::PASSWORD, 'master_password' => self::MASTER,
        ])->assertForbidden();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_api_mfa_accounts_cannot_obtain_a_token_directly(): void
    {
        [$user] = $this->registeredUser();
        $user->mfa()->create(['type' => 'email', 'actif' => true]);
        $this->postJson('/api/auth/login', [
            'email' => $user->email, 'password' => self::PASSWORD, 'master_password' => self::MASTER,
        ])->assertStatus(428)->assertJsonPath('error', 'mfa_required');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public static function protectedEndpoints(): array
    {
        return [['/api/services'], ['/api/auth/oauth-data']];
    }

    #[DataProvider('protectedEndpoints')]
    public function test_scoped_token_can_use_extension_endpoint(string $uri): void
    {
        [$user] = $this->registeredUser();
        $this->withToken($user->createToken('audit', ['read:services'])->plainTextToken)->getJson($uri)->assertOk();
    }

    #[DataProvider('protectedEndpoints')]
    public function test_token_without_required_ability_is_denied(string $uri): void
    {
        [$user] = $this->registeredUser();
        $this->withToken($user->createToken('audit', [])->plainTextToken)->getJson($uri)->assertForbidden();
    }

    public function test_profile_handles_uninitialized_oauth_user(): void
    {
        $user = $this->user();
        $this->withToken($user->createToken('audit', ['read:services'])->plainTextToken)
            ->getJson('/api/profil')->assertUnprocessable();
    }

    public function test_handoff_code_is_single_use(): void
    {
        $user = $this->user();
        $service = app(ExtensionHandoffService::class);
        $code = $service->genererCode($user);
        self::assertSame(64, strlen($code));
        $this->postJson('/api/auth/extension/echanger-code', ['code' => $code])->assertOk()->assertJsonStructure(['token']);
        $this->postJson('/api/auth/extension/echanger-code', ['code' => $code])->assertUnprocessable();
        self::assertSame(1, $user->tokens()->count());
    }

    public function test_handoff_expires_after_sixty_seconds(): void
    {
        $service = app(ExtensionHandoffService::class);
        $code = $service->genererCode($this->user());
        $this->travel(61)->seconds();
        self::assertNull($service->resoudreCode($code));
    }

    public function test_unlisted_extension_redirect_is_rejected(): void
    {
        config(['services.extension.redirect_urls' => 'https://extension.example.test/callback']);
        $this->get('/auth/google/redirect?extension_redirect=https://attacker.example.test/callback')->assertStatus(400);
        self::assertNull(session('extension_redirect'));
    }

    public function test_invalid_handoff_cannot_create_a_token(): void
    {
        $this->postJson('/api/auth/extension/echanger-code', ['code' => 'invalid'])->assertUnprocessable();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
