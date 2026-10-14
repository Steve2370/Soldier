<?php

namespace Tests\Feature\Audit;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacySchemaAuditTestCase;

class HttpSurfaceTest extends LegacySchemaAuditTestCase
{
    public static function publicPages(): array
    {
        return array_map(fn ($uri) => [$uri], ['/', '/welcome', '/connexion', '/inscription', '/pricing', '/privacy', '/up']);
    }

    #[DataProvider('publicPages')]
    public function test_public_page_renders(string $uri): void
    {
        $this->get($uri)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_every_authenticated_route_rejects_guests(): void
    {
        $checked = 0;
        $failures = [];
        foreach (Route::getRoutes() as $route) {
            if (! array_intersect(['auth', 'auth:sanctum'], $route->gatherMiddleware())) {
                continue;
            }
            $uri = '/'.preg_replace('/\{[^}]+\}/', '999999', $route->uri());
            try {
                $response = $this->json($route->methods()[0], $uri);
                if ($response->status() !== 401) {
                    $failures[] = $uri.': HTTP '.$response->status();
                }
            } catch (\Throwable $exception) {
                $failures[] = $uri.': '.$exception->getMessage();
            }
            $checked++;
        }
        self::assertGreaterThanOrEqual(50, $checked, 'Unexpected authenticated route inventory shrinkage.');
        self::assertSame([], $failures, implode("\n", $failures));
    }

    public static function unlockedPages(): array
    {
        return array_map(fn ($uri) => [$uri], [
            '/dashboard', '/services/creer', '/generateur', '/partage', '/settings', '/settings/sessions',
            '/oauth/master-password', '/email/verify', '/pricing/succes',
        ]);
    }

    #[DataProvider('unlockedPages')]
    public function test_authenticated_page_renders(string $uri): void
    {
        $user = $this->user();
        [, , $kek] = $this->vault($user);
        $this->unlock($user, $kek);
        $this->get($uri)->assertOk();
    }

    public static function administratorPages(): array
    {
        return [['/admin'], ['/admin/logs'], ['/admin/logs/export']];
    }

    #[DataProvider('administratorPages')]
    public function test_administrator_pages_render(string $uri): void
    {
        $admin = $this->user();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin)->get($uri)->assertOk();
    }

    public function test_unverified_user_cannot_open_vault(): void
    {
        $user = $this->user(['email_verified_at' => null]);
        $this->unlock($user, random_bytes(32));
        $this->get('/dashboard')->assertRedirect(route('verification.notice'));
    }

    public function test_locked_vault_rejects_json_access(): void
    {
        $this->actingAs($this->user())->getJson('/dashboard')->assertStatus(423);
    }

    public function test_locked_session_has_a_reachable_unlock_page(): void
    {
        $this->actingAs($this->user());
        $response = $this->get('/dashboard');
        for ($hop = 0; $hop < 5 && $response->isRedirect(); $hop++) {
            $response = $this->get($response->headers->get('Location'));
        }
        $response->assertOk();
    }

    public function test_invalid_session_key_is_treated_as_locked(): void
    {
        $this->actingAs($this->user())->withSession([
            'crypto.kek' => 'invalid-base64', 'crypto.cle_privee' => 'invalid-key',
        ])->getJson('/dashboard')->assertStatus(423);
    }

    public function test_pending_mfa_prevents_access_even_if_old_vault_keys_exist(): void
    {
        $user = $this->user();
        $this->unlock($user, random_bytes(32));
        $user->mfa()->create(['type' => 'email', 'actif' => true]);
        $this->withSession(['auth.mfa_user_id_pending' => $user->id, 'auth.mfa_verifie' => false]);
        $this->getJson('/dashboard')->assertStatus(423);
    }

    public function test_private_pages_forbid_browser_caching(): void
    {
        $this->unlock($this->user(), random_bytes(32));
        self::assertStringContainsString('no-store', $this->get('/settings')->headers->get('Cache-Control'));
    }

    public function test_user_cannot_assign_admin_privileges_through_registration(): void
    {
        $this->post('/inscription', [
            'name' => 'Audit User', 'email' => 'admin-injection@example.test',
            'password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD,
            'master_password' => self::MASTER, 'is_admin' => true,
        ])->assertRedirect(route('verification.notice'));
        self::assertFalse(User::where('email', 'admin-injection@example.test')->firstOrFail()->is_admin);
    }
}
