<?php

namespace Tests\Feature\Audit;

use App\Exceptions\InvalidMasterPasswordException;
use App\Helpers\SessionHelper;
use App\Mail\CodeMfaMail;
use App\Services\Auth\MfaService;
use App\Services\Coffre\CleManagementService;
use App\Services\Coffre\CoffreService;
use App\Services\Crypto\AesEncryptionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FA\Google2FA;
use Tests\Support\LegacySchemaAuditTestCase;

class SettingsAndMfaTest extends LegacySchemaAuditTestCase
{
    public function test_master_password_rotation_preserves_vault_and_private_key(): void
    {
        [$user, $keys] = $this->registeredUser();
        $vault = $user->coffres()->sole();
        $key = app(CleManagementService::class)->dechiffrerDataKeyCoffre($vault->data_key_encrypted, $keys['kek']);
        $element = $this->element($vault, $key);
        $this->unlock($user, $keys['kek'], $keys['cle_privee']);
        $this->post('/settings/mot-de-passe', [
            'ancien_master_password' => self::MASTER,
            'nouveau_master_password' => 'new-master-password',
            'nouveau_master_password_confirmation' => 'new-master-password',
        ])->assertRedirect(route('settings'));
        $newKeys = app(CleManagementService::class)->deverouillerCles($user->fresh(), 'new-master-password');
        self::assertSame($keys, $newKeys);
        self::assertSame('vault-secret-value', app(CoffreService::class)->lireElement($element, $key)['donnees']['mot_de_passe']);
        $this->expectException(InvalidMasterPasswordException::class);
        app(CleManagementService::class)->deverouillerCles($user->fresh(), self::MASTER);
    }

    public function test_account_password_change_requires_current_password(): void
    {
        $user = $this->user();
        $this->unlock($user, random_bytes(32));
        $this->post('/settings/mot-de-passe/compte', [
            'mot_de_passe_actuel' => 'wrong', 'nouveau_mot_de_passe' => 'replacement-password',
            'nouveau_mot_de_passe_confirmation' => 'replacement-password',
        ])->assertSessionHasErrors('mot_de_passe_actuel');
        self::assertTrue(Hash::check(self::PASSWORD, $user->fresh()->password));
        $this->post('/settings/mot-de-passe/compte', [
            'mot_de_passe_actuel' => self::PASSWORD, 'nouveau_mot_de_passe' => 'replacement-password',
            'nouveau_mot_de_passe_confirmation' => 'replacement-password',
        ])->assertRedirect(route('settings'));
        self::assertTrue(Hash::check('replacement-password', $user->fresh()->password));
    }

    public function test_avatar_upload_replacement_and_removal(): void
    {
        Storage::fake('public');
        $user = $this->user();
        $this->unlock($user, random_bytes(32));
        $this->post('/settings/avatar', ['avatar' => UploadedFile::fake()->image('avatar.png')])->assertRedirect();
        $oldPath = $user->fresh()->avatar;
        Storage::disk('public')->assertExists($oldPath);
        $this->post('/settings/avatar', ['avatar' => UploadedFile::fake()->image('new.png')])->assertRedirect();
        Storage::disk('public')->assertMissing($oldPath);
        $newPath = $user->fresh()->avatar;
        Storage::disk('public')->assertExists($newPath);
        $this->delete('/settings/avatar')->assertRedirect();
        Storage::disk('public')->assertMissing($newPath);
        self::assertNull($user->fresh()->avatar);
    }

    public function test_avatar_rejects_non_images(): void
    {
        Storage::fake('public');
        $this->unlock($this->user(), random_bytes(32));
        $this->postJson('/settings/avatar', ['avatar' => UploadedFile::fake()->create('payload.php', 1, 'application/x-php')])
            ->assertUnprocessable()->assertJsonValidationErrors('avatar');
        self::assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_session_revocation_is_scoped_to_current_user(): void
    {
        $user = $this->user();
        $other = $this->user();
        foreach (['own-session' => $user, 'foreign-session' => $other] as $id => $owner) {
            DB::table('sessions')->insert(['id' => $id, 'user_id' => $owner->id, 'payload' => '', 'last_activity' => time()]);
        }
        $this->unlock($user, random_bytes(32));
        $this->delete('/settings/sessions/foreign-session')->assertRedirect();
        $this->assertDatabaseHas('sessions', ['id' => 'foreign-session']);
        $this->delete('/settings/sessions/own-session')->assertRedirect();
        $this->assertDatabaseMissing('sessions', ['id' => 'own-session']);
    }

    public function test_email_mfa_code_is_hashed_single_use_and_expires(): void
    {
        $user = $this->user();
        $service = app(MfaService::class);
        $service->envoyerCodeEmail($user);
        $code = Mail::sent(CodeMfaMail::class)->sole()->code;
        self::assertNotSame($code, $user->mfa()->sole()->code_hash);
        self::assertTrue($service->verifierCode($user, $code));
        self::assertFalse($service->verifierCode($user, $code));
        $service->envoyerCodeEmail($user);
        $code = Mail::sent(CodeMfaMail::class)->last()->code;
        $this->travel(11)->minutes();
        self::assertFalse($service->verifierCode($user, $code));
    }

    public function test_email_mfa_locks_after_five_wrong_codes(): void
    {
        $user = $this->user();
        $service = app(MfaService::class);
        $service->envoyerCodeEmail($user);
        $code = Mail::sent(CodeMfaMail::class)->sole()->code;
        $wrong = $code === '000000' ? '111111' : '000000';
        for ($i = 0; $i < 5; $i++) {
            self::assertFalse($service->verifierCode($user, $wrong));
        }
        self::assertFalse($service->verifierCode($user, $code));
    }

    public function test_totp_setup_validate_and_verify_with_pending_keys(): void
    {
        [$user, $keys] = $this->registeredUser();
        $this->unlock($user, $keys['kek'], $keys['cle_privee']);
        $response = $this->getJson('/settings/totp/configurer')->assertOk()->assertJsonStructure(['secret', 'otpauth_url']);
        $secret = $response->json('secret');
        $code = app(Google2FA::class)->getCurrentOtp($secret);
        $this->post('/settings/totp/valider', ['code' => $code])->assertRedirect(route('settings'));
        $record = $user->mfa()->where('type', 'totp')->sole();
        self::assertTrue($record->actif);
        self::assertStringNotContainsString($secret, $record->totp_secret_chiffre);
        self::assertCount(8, $record->codes_recuperation);
        SessionHelper::effacerCles();
        SessionHelper::stockerMfaPendante($keys['kek'], $keys['cle_privee'], 'totp');
        self::assertFalse(app(MfaService::class)->verifierCode($user, $code), 'The setup code was already consumed.');
        $nextCode = app(Google2FA::class)->oathTotp($secret, $record->last_totp_step + 1);
        self::assertTrue(app(MfaService::class)->verifierCode($user, $nextCode));
    }

    public function test_totp_cannot_be_replayed_after_success(): void
    {
        $user = $this->user();
        $kek = random_bytes(32);
        $secret = app(Google2FA::class)->generateSecretKey();
        $user->mfa()->create([
            'type' => 'totp', 'actif' => true,
            'totp_secret_chiffre' => json_encode(app(AesEncryptionService::class)->encrypt($secret, $kek)),
        ]);
        SessionHelper::stockerMfaPendante($kek, 'audit-private-key', 'totp');
        $code = app(Google2FA::class)->getCurrentOtp($secret);
        self::assertTrue(app(MfaService::class)->verifierCode($user, $code));
        self::assertFalse(app(MfaService::class)->verifierCode($user, $code));
    }

    public function test_account_deletion_requires_password_and_removes_keys_and_tokens(): void
    {
        [$user, $keys] = $this->registeredUser();
        $user->createToken('audit');
        $this->unlock($user, $keys['kek'], $keys['cle_privee']);
        $this->delete('/settings/compte', ['password_confirmation' => 'wrong'])->assertSessionHasErrors('password_confirmation');
        $this->assertDatabaseCount('cles_user', 1);
        $this->delete('/settings/compte', ['password_confirmation' => self::PASSWORD])->assertRedirect(route('welcome'));
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertDatabaseCount('cles_user', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertGuest();
    }
}
