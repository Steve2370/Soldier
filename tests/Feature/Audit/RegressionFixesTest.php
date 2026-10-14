<?php

namespace Tests\Feature\Audit;

use App\Helpers\SessionHelper;
use App\Mail\CodeMfaMail;
use App\Models\ActivityLog;
use App\Models\ElementCoffre;
use App\Models\FamilyGroup;
use App\Models\InvitationPartage;
use App\Models\User;
use App\Services\Auth\ExtensionHandoffService;
use App\Services\Auth\MfaService;
use App\Services\Coffre\CleManagementService;
use App\Services\Coffre\CoffreService;
use App\Services\Famille\FamilyAccessRevocationService;
use App\Services\Famille\FamilyMembershipService;
use App\Services\Logs\ActivityLogService;
use App\Services\Notifications\Contracts\MailDeliveryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as OAuthUser;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacySchemaAuditTestCase;

class RegressionFixesTest extends LegacySchemaAuditTestCase
{
    public function test_schema_repair_is_idempotent_and_preserves_existing_accounts_tokens_and_logs(): void
    {
        $user = $this->user();
        $token = $user->createToken('existing', ['read:services']);
        ActivityLogService::log('existing', 'Existing event', $user->id, ['context' => ['count' => 2]]);
        $migration = require database_path('migrations/2026_10_14_000001_repair_security_schema.php');
        $migration->up();
        $migration->up();
        self::assertSame($user->id, User::findOrFail($user->id)->id);
        self::assertSame(hash('sha256', explode('|', $token->plainTextToken)[1]), $user->tokens()->sole()->token);
        self::assertSame(['context' => ['count' => 2]], ActivityLog::sole()->metadata);
        self::assertTrue(Schema::hasColumn('mfa', 'last_totp_step'));
    }

    public function test_schema_repair_refuses_destructive_rollback(): void
    {
        $migration = require database_path('migrations/2026_10_14_000001_repair_security_schema.php');
        $this->expectException(\RuntimeException::class);
        $migration->down();
    }

    public function test_schema_repair_fills_partial_legacy_columns_without_changing_keys_or_privileges(): void
    {
        [$user] = $this->registeredUser();
        $user->forceFill(['is_admin' => true])->save();
        $keys = DB::table('cles_user')->where('user_id', $user->id)->first();
        $token = $user->createToken('historical', ['read:services']);
        Schema::table('personal_access_tokens', fn ($table) => $table->dropColumn(['last_used_at', 'expires_at']));
        Schema::table('mfa', fn ($table) => $table->dropColumn('last_totp_step'));
        $migration = require database_path('migrations/2026_10_14_000001_repair_security_schema.php');
        $migration->up();
        self::assertTrue($user->fresh()->is_admin);
        self::assertEquals($keys, DB::table('cles_user')->where('user_id', $user->id)->first());
        self::assertSame(hash('sha256', explode('|', $token->plainTextToken)[1]), $user->tokens()->sole()->token);
        self::assertTrue(Schema::hasColumns('personal_access_tokens', ['last_used_at', 'expires_at']));
        self::assertTrue(Schema::hasColumn('mfa', 'last_totp_step'));
    }

    public function test_legacy_mixed_case_email_can_log_in_without_rewriting_the_account(): void
    {
        [$user] = $this->registeredUser();
        DB::table('users')->where('id', $user->id)->update(['email' => 'Legacy@Example.test']);
        $this->withSession(['login_email' => 'legacy@example.test'])->post('/connexion/password', [
            'password' => self::PASSWORD, 'master_password' => self::MASTER,
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_rejects_a_case_variant_of_a_legacy_email(): void
    {
        $user = $this->user();
        DB::table('users')->where('id', $user->id)->update(['email' => 'Legacy@Example.test']);
        $this->post('/inscription', ['name' => 'Duplicate', 'email' => 'legacy@example.test',
            'password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD,
            'master_password' => self::MASTER])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_recovery_code_is_consumed_and_cannot_be_replayed(): void
    {
        [$user, $keys] = $this->registeredUser();
        $user->mfa()->create(['type' => 'totp', 'actif' => true]);
        $service = app(MfaService::class);
        $codes = $service->genererCodesRecuperation($user);
        SessionHelper::stockerMfaPendante($keys['kek'], $keys['cle_privee'], 'totp');
        self::assertTrue($service->verifierCode($user, $codes[0]));
        self::assertCount(7, $user->mfa()->sole()->codes_recuperation);
        self::assertFalse($service->verifierCode($user, $codes[0]));
        self::assertTrue($service->verifierCode($user, $codes[1]));
    }

    public function test_expired_mfa_session_cannot_consume_a_recovery_code(): void
    {
        [$user, $keys] = $this->registeredUser();
        $user->mfa()->create(['type' => 'totp', 'actif' => true]);
        $code = app(MfaService::class)->genererCodesRecuperation($user)[0];
        SessionHelper::stockerMfaPendante($keys['kek'], $keys['cle_privee'], 'totp');
        SessionHelper::mfaUserIdPending($user->id);
        $this->travel(11)->minutes();
        $this->post('/verification-mfa', ['code' => $code])->assertRedirect(route('connexion'));
        $this->assertGuest();
        self::assertCount(8, $user->mfa()->sole()->codes_recuperation);
    }

    public function test_mfa_resend_delivers_a_new_challenge_and_preserves_guest_state(): void
    {
        [$user, $keys] = $this->registeredUser();
        $user->mfa()->create(['type' => 'email', 'actif' => true]);
        SessionHelper::stockerMfaPendante($keys['kek'], $keys['cle_privee'], 'email');
        SessionHelper::mfaUserIdPending($user->id);
        $this->post('/verification-mfa/renvoyer')->assertRedirect(route('mfa.verify'));
        $this->assertGuest();
        Mail::assertSent(CodeMfaMail::class);
    }

    public function test_positive_google_claim_is_accepted_and_clears_previous_vault_keys(): void
    {
        $oauth = (new OAuthUser)->setRaw(['email_verified' => true])->map([
            'id' => 'google-verified', 'email' => 'verified@example.test', 'name' => 'Verified',
        ]);
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($oauth);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        $this->withSession(['crypto.kek' => base64_encode(random_bytes(32)), 'crypto.cle_privee' => 'old', 'auth.mfa_verifie' => true]);
        $this->get('/auth/google/callback')->assertRedirect(route('oauth.master-password'));
        $this->assertAuthenticated();
        self::assertNull(SessionHelper::obtenirKek());
        self::assertFalse(SessionHelper::mfaVerifie());
    }

    public function test_invalid_private_key_cannot_unlock_a_session_with_a_valid_kek(): void
    {
        $this->actingAs($this->user())->withSession(['crypto.kek' => base64_encode(random_bytes(32)), 'crypto.cle_privee' => 'invalid'])
            ->getJson('/dashboard')->assertStatus(423);
    }

    public function test_empty_share_selection_denies_http_api_and_direct_web_access(): void
    {
        $owner = $this->user();
        $recipient = $this->user();
        [$vault, $key] = $this->vault($owner);
        $element = $this->element($vault, $key);
        [, $privateKey] = $this->share($owner, $recipient, $vault, $key, ['element_ids' => []]);
        $token = $recipient->createToken('audit', ['read:services'])->plainTextToken;
        $this->withToken($token)->getJson('/api/services')->assertOk()->assertJsonPath('total', 0);
        $this->unlock($recipient, random_bytes(32), $privateKey);
        $this->getJson('/services/'.$element->id)->assertForbidden();
    }

    public static function restrictedMutations(): array
    {
        return [['DELETE', ''], ['PATCH', '/favori']];
    }

    #[DataProvider('restrictedMutations')]
    public function test_unselected_item_cannot_be_deleted_or_favorited(string $method, string $suffix): void
    {
        $owner = $this->user();
        $recipient = $this->user();
        [$vault, $key] = $this->vault($owner);
        $selected = $this->element($vault, $key);
        $other = $this->element($vault, $key);
        [, $private] = $this->share($owner, $recipient, $vault, $key, ['element_ids' => [$selected->id], 'permission' => 'ecriture']);
        $this->unlock($recipient, random_bytes(32), $private);
        $this->json($method, '/services/'.$other->id.$suffix)->assertForbidden();
        self::assertNull($other->fresh()->deleted_at);
        self::assertFalse($other->fresh()->favori);
    }

    public function test_trash_restore_preserves_ciphertext_and_is_owner_scoped(): void
    {
        $user = $this->user();
        [$vault, $key, $kek] = $this->vault($user);
        $element = $this->element($vault, $key);
        $cipher = $element->payload_encrypted;
        $this->unlock($user, $kek);
        $this->delete('/services/'.$element->id)->assertRedirect();
        $this->get('/corbeille')->assertOk()->assertSee($element->label);
        $this->unlock($this->user(), random_bytes(32));
        $this->patch('/corbeille/'.$element->id.'/restaurer')->assertNotFound();
        $this->unlock($user, $kek);
        $this->patch('/corbeille/'.$element->id.'/restaurer')->assertRedirect(route('corbeille'));
        self::assertSame($cipher, $element->fresh()->payload_encrypted);
        self::assertSame('vault-secret-value', app(CoffreService::class)->lireElement($element->fresh(), $key)['donnees']['mot_de_passe']);
    }

    public function test_trash_retention_excludes_live_and_recent_items(): void
    {
        $user = $this->user();
        [$vault, $key, $kek] = $this->vault($user);
        $old = $this->element($vault, $key);
        $old->delete();
        $this->travel(31)->days();
        $recent = $this->element($vault, $key);
        $recent->delete();
        $live = $this->element($vault, $key);
        $this->unlock($user, $kek);
        $this->patch('/corbeille/'.$old->id.'/restaurer')->assertNotFound();
        $this->artisan('model:prune', ['--model' => [ElementCoffre::class]])->assertSuccessful();
        self::assertNull(ElementCoffre::withTrashed()->find($old->id));
        self::assertNotNull(ElementCoffre::withTrashed()->find($recent->id));
        self::assertNotNull(ElementCoffre::find($live->id));
    }

    public function test_explicit_blank_password_is_preserved_but_notes_and_url_can_be_cleared(): void
    {
        $user = $this->user();
        [$vault, $key, $kek] = $this->vault($user);
        $element = $this->element($vault, $key, ['url' => 'https://example.test']);
        $this->unlock($user, $kek);
        $this->put('/services/'.$element->id, ['type' => 'login', 'label' => 'Updated', 'mot_de_passe' => '', 'notes' => '', 'url' => ''])->assertRedirect();
        $decoded = app(CoffreService::class)->lireElement($element->fresh(), $key)['donnees'];
        self::assertSame('vault-secret-value', $decoded['mot_de_passe']);
        self::assertEmpty($decoded['notes'] ?? null);
        self::assertNull($element->fresh()->url);
    }

    public function test_changing_an_existing_element_type_is_rejected_without_data_loss(): void
    {
        $user = $this->user();
        [$vault, $key, $kek] = $this->vault($user);
        $element = $this->element($vault, $key);
        $this->unlock($user, $kek);
        $this->putJson('/services/'.$element->id, ['type' => 'carte', 'label' => 'Changed'])->assertUnprocessable();
        self::assertSame($element->payload_encrypted, $element->fresh()->payload_encrypted);
    }

    public function test_family_removal_revokes_tagged_invites_and_shares_but_preserves_personal_shares(): void
    {
        $owner = $this->user();
        $recipient = $this->user();
        [$familyVault, $familyKey] = $this->vault($owner);
        [$otherVault, $otherKey] = $this->vault($owner);
        [$personalVault, $personalKey] = $this->vault($owner);
        $group = FamilyGroup::create(['owner_id' => $owner->id, 'nom' => 'Test', 'coffre_id' => $familyVault->id]);
        $member = $group->members()->create(['user_id' => $recipient->id, 'role' => 'member']);
        [$familyShare] = $this->share($owner, $recipient, $familyVault, $familyKey);
        [$tagged] = $this->share($owner, $recipient, $otherVault, $otherKey, ['family_group_id' => $group->id]);
        [$personal] = $this->share($owner, $recipient, $personalVault, $personalKey);
        $invitation = InvitationPartage::create(['coffre_id' => $otherVault->id, 'expediteur_id' => $owner->id,
            'email_destinataire' => $recipient->email, 'family_group_id' => $group->id, 'token_hash' => hash('sha256', 'audit'),
            'data_key_encrypted' => 'synthetic', 'permission' => 'lecture', 'statut' => 'en_attente', 'expire_le' => now()->addDay()]);
        app(FamilyAccessRevocationService::class)->remove($member);
        self::assertNull($familyShare->fresh());
        self::assertNull($tagged->fresh());
        self::assertNotNull($personal->fresh());
        self::assertSame('expiree', $invitation->fresh()->statut);
    }

    public function test_family_join_without_recipient_keys_creates_no_membership_or_share(): void
    {
        $owner = $this->user();
        [$vault, , $kek] = $this->vault($owner);
        $group = FamilyGroup::create(['owner_id' => $owner->id, 'nom' => 'Test', 'coffre_id' => $vault->id]);
        try {
            app(FamilyMembershipService::class)->add($group, $this->user(), $kek);
            self::fail('Missing recipient keys should reject the join.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('family_members', 0);
            $this->assertDatabaseCount('shares_coffre', 0);
        }
    }

    public function test_historical_family_share_without_membership_is_no_longer_active(): void
    {
        $owner = $this->user();
        $recipient = $this->user();
        [$vault, $key] = $this->vault($owner);
        $group = FamilyGroup::create(['owner_id' => $owner->id, 'nom' => 'Historical', 'coffre_id' => $vault->id]);
        [$share, $private] = $this->share($owner, $recipient, $vault, $key);
        $element = $this->element($vault, $key);
        $this->unlock($recipient, random_bytes(32), $private);
        $this->getJson('/services/'.$element->id)->assertForbidden();
        self::assertFalse($share->isValide());
        $group->members()->create(['user_id' => $recipient->id, 'role' => 'member']);
        self::assertTrue($share->isValide());
        $this->getJson('/services/'.$element->id)->assertOk();
    }

    public function test_old_family_invitation_cannot_restore_access_for_a_departed_member(): void
    {
        $owner = $this->user();
        $recipient = $this->user();
        [$vault] = $this->vault($owner);
        FamilyGroup::create(['owner_id' => $owner->id, 'nom' => 'Historical', 'coffre_id' => $vault->id]);
        InvitationPartage::create(['coffre_id' => $vault->id, 'expediteur_id' => $owner->id,
            'email_destinataire' => $recipient->email, 'token_hash' => hash('sha256', 'old-family-token'),
            'data_key_encrypted' => 'synthetic', 'permission' => 'lecture', 'statut' => 'en_attente', 'expire_le' => now()->addDay()]);
        $this->unlock($recipient, random_bytes(32));
        $this->getJson('/invitation/old-family-token')->assertForbidden();
        $this->assertDatabaseCount('shares_coffre', 0);
    }

    public function test_passkey_challenge_expires_and_is_consumed_on_invalid_submission(): void
    {
        $this->postJson('/passkeys/options-connexion')->assertOk();
        $this->travel(61)->seconds();
        $this->postJson('/passkeys/connecter', ['credential' => ['id' => 'unknown']])->assertUnprocessable();
        self::assertNull(session('passkey_challenge_auth'));
    }

    public function test_sensitive_values_are_not_flashed_after_invalid_account_registration(): void
    {
        $this->post('/inscription', ['name' => '', 'email' => 'bad', 'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD, 'master_password' => self::MASTER])->assertSessionHasErrors();
        foreach (['password', 'password_confirmation', 'master_password'] as $key) {
            self::assertNull(session()->getOldInput($key));
        }
    }

    public function test_master_password_change_preserves_significant_whitespace(): void
    {
        [$user, $keys] = $this->registeredUser();
        $this->unlock($user, $keys['kek'], $keys['cle_privee']);
        $newPassword = '  significant-whitespace-password  ';
        $this->post('/settings/mot-de-passe', ['ancien_master_password' => self::MASTER,
            'nouveau_master_password' => $newPassword, 'nouveau_master_password_confirmation' => $newPassword])
            ->assertRedirect(route('settings'))->assertSessionHasNoErrors();
        $unlocked = app(CleManagementService::class)->deverouillerCles($user->fresh(), $newPassword);
        self::assertSame($keys['cle_privee'], $unlocked['cle_privee']);
        sodium_memzero($unlocked['kek']);
        sodium_memzero($unlocked['cle_privee']);
    }

    public function test_verification_resend_transport_failure_preserves_account_without_500(): void
    {
        $user = $this->user(['email_verified_at' => null]);
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('Synthetic SMTP outage'));
        $this->actingAs($user)->from('/email/verify')->post('/email/verification-notification')
            ->assertRedirect('/email/verify')->assertSessionHas('toast.type', 'warning');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'email_verified_at' => null]);
    }

    public function test_handoff_exchange_rejects_an_email_that_is_no_longer_verified(): void
    {
        $user = $this->user(['email_verified_at' => null]);
        $code = app(ExtensionHandoffService::class)->genererCode($user);
        $this->postJson('/api/auth/extension/echanger-code', ['code' => $code])->assertUnprocessable();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_invitation_transport_failure_does_not_report_email_delivery(): void
    {
        [$owner, $keys] = $this->registeredUser();
        [$recipient] = $this->registeredUser();
        [$vault, $key] = $this->vault($owner, $keys['kek']);
        $element = $this->element($vault, $key);
        $this->instance(MailDeliveryInterface::class,
            Mockery::mock(MailDeliveryInterface::class)->shouldReceive('send')->once()->andReturn(false)->getMock());
        $this->unlock($owner, $keys['kek'], $keys['cle_privee']);
        $this->post('/partage', ['coffre_id' => $vault->id, 'element_ids' => [$element->id],
            'permission' => 'lecture', 'email' => $recipient->email])->assertRedirect()->assertSessionHas('toast.type', 'warning');
        $this->assertDatabaseCount('invitations_partage', 1);
    }
}
