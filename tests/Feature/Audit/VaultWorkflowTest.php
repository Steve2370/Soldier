<?php

namespace Tests\Feature\Audit;

use App\Services\Coffre\CoffreService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacySchemaAuditTestCase;

class VaultWorkflowTest extends LegacySchemaAuditTestCase
{
    public static function elementTypes(): array
    {
        return [
            'login' => ['login', 'mot_de_passe', 'Secret-Login-2026'],
            'card' => ['carte', 'numero', '4242424242424242'],
            'note' => ['note', 'contenu', 'Une note privée'],
            'identity' => ['identite', 'passeport', 'AUDIT-PASSPORT'],
            'ssh' => ['cles', 'cle_privee', 'synthetic-private-key'],
            'other' => ['autre', 'contenu', 'synthetic-api-token'],
        ];
    }

    #[DataProvider('elementTypes')]
    public function test_create_encrypt_read_and_edit_each_type(string $type, string $field, string $secret): void
    {
        $user = $this->user();
        [$vault, $key, $kek] = $this->vault($user);
        $this->unlock($user, $kek);
        $payload = ['type' => $type, 'label' => 'Élément '.$type, 'coffre_id' => $vault->id, $field => $secret];
        $this->post('/services', $payload)->assertRedirect(route('dashboard'));
        $element = $vault->elements()->sole();
        self::assertStringNotContainsString($secret, $element->payload_encrypted);
        $decoded = app(CoffreService::class)->lireElement($element, $key)['donnees'];
        self::assertSame($secret, $decoded[$field] ?? null);
        $this->get('/services/'.$element->id)->assertOk()->assertSee($secret);
        $this->get('/services/'.$element->id.'/modifier')->assertOk();
        $this->put('/services/'.$element->id, array_merge($payload, [$field => $secret.'-new']))->assertRedirect();
        $decoded = app(CoffreService::class)->lireElement($element->fresh(), $key)['donnees'];
        self::assertSame($secret.'-new', $decoded[$field] ?? null);
    }

    public function test_user_can_clear_an_optional_note(): void
    {
        $user = $this->user();
        [$vault, $key, $kek] = $this->vault($user);
        $element = $this->element($vault, $key);
        $this->unlock($user, $kek);
        $this->put('/services/'.$element->id, ['type' => 'login', 'label' => 'Modifié', 'notes' => ''])->assertRedirect();
        self::assertEmpty(app(CoffreService::class)->lireElement($element->fresh(), $key)['donnees']['notes'] ?? null);
    }

    public function test_omitted_password_is_preserved_when_editing_label(): void
    {
        $user = $this->user();
        [$vault, $key, $kek] = $this->vault($user);
        $element = $this->element($vault, $key);
        $this->unlock($user, $kek);
        $this->put('/services/'.$element->id, ['type' => 'login', 'label' => 'Renommé'])->assertRedirect();
        self::assertSame('vault-secret-value', app(CoffreService::class)->lireElement($element->fresh(), $key)['donnees']['mot_de_passe']);
    }

    public function test_editing_ssh_label_preserves_passphrase(): void
    {
        $user = $this->user();
        [$vault, $key, $kek] = $this->vault($user);
        $element = $this->element($vault, $key, ['type' => 'cles', 'passphrase_ssh' => 'ssh-secret']);
        $this->unlock($user, $kek);
        $this->put('/services/'.$element->id, ['type' => 'cles', 'label' => 'Renommé'])->assertRedirect();
        self::assertSame('ssh-secret', app(CoffreService::class)->lireElement($element->fresh(), $key)['donnees']['mot_de_passe'] ?? null);
    }

    public function test_custom_fields_survive_http_validation(): void
    {
        $user = $this->user();
        [$vault, $key, $kek] = $this->vault($user);
        $this->unlock($user, $kek);
        $fields = [['nom' => 'Client', 'valeur' => '12345']];
        $this->post('/services', ['type' => 'login', 'label' => 'Custom', 'champs_personnels' => $fields])->assertRedirect();
        self::assertSame($fields, app(CoffreService::class)->lireElement($vault->elements()->sole(), $key)['donnees']['champs_personnels']);
    }

    public function test_delete_retains_the_item_for_the_trash_promised_by_the_ui(): void
    {
        $user = $this->user();
        [$vault, $key, $kek] = $this->vault($user);
        $element = $this->element($vault, $key);
        $this->unlock($user, $kek);
        $this->delete('/services/'.$element->id)->assertRedirect();
        $this->assertSoftDeleted('elements_coffres', ['id' => $element->id]);
    }

    public function test_favorite_toggles_both_directions(): void
    {
        $user = $this->user();
        [$vault, $key, $kek] = $this->vault($user);
        $element = $this->element($vault, $key);
        $this->unlock($user, $kek);
        $this->patchJson('/services/'.$element->id.'/favori')->assertOk()->assertJsonPath('favori', true);
        $this->patchJson('/services/'.$element->id.'/favori')->assertOk()->assertJsonPath('favori', false);
    }

    public static function invalidFields(): array
    {
        return [
            ['url', 'javascript:alert(1)'], ['url', 'file:///etc/passwd'],
            ['type', 'admin'], ['port', 0], ['port', 65536], ['label', str_repeat('x', 201)],
        ];
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_service_data_is_rejected(string $field, mixed $value): void
    {
        $this->unlock($this->user(), random_bytes(32));
        $this->postJson('/services', array_merge(['type' => 'login', 'label' => 'Valid'], [$field => $value]))
            ->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('elements_coffres', 0);
    }

    public function test_user_cannot_create_a_service_in_another_users_vault(): void
    {
        [$vault] = $this->vault($this->user());
        $this->unlock($this->user(), random_bytes(32));
        $this->postJson('/services', ['type' => 'login', 'label' => 'Attack', 'coffre_id' => $vault->id])->assertNotFound();
        $this->assertDatabaseCount('elements_coffres', 0);
    }

    public function test_validation_does_not_flash_vault_secrets_into_old_input(): void
    {
        $this->unlock($this->user(), random_bytes(32));
        $this->from('/services/creer')->post('/services', [
            'type' => 'cles', 'label' => '', 'mot_de_passe' => 'audit-password',
            'cle_privee' => 'audit-private-key', 'passphrase_ssh' => 'audit-passphrase',
        ])->assertSessionHasErrors('label');
        foreach (['mot_de_passe', 'cle_privee', 'passphrase_ssh'] as $field) {
            self::assertNull(session()->getOldInput($field), "Secret flashed: {$field}");
        }
    }
}
