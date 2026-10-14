<?php

namespace Tests\Feature\Audit;

use App\Http\Controllers\Api\ServicesApiController;
use App\Mail\InvitationPartageMail;
use App\Models\InvitationPartage;
use App\Policies\ElementCoffrePolicy;
use App\Services\Coffre\CleManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacySchemaAuditTestCase;

class SharingAuthorizationTest extends LegacySchemaAuditTestCase
{
    public static function accessMatrix(): array
    {
        return [
            'reader' => ['lecture', 'accepte', false, true, false],
            'editor' => ['ecriture', 'accepte', false, true, true],
            'expired reader' => ['lecture', 'accepte', true, false, false],
            'expired editor' => ['ecriture', 'accepte', true, false, false],
            'revoked' => ['ecriture', 'revoque', false, false, false],
            'pending' => ['ecriture', 'en_entente', false, false, false],
            'refused' => ['ecriture', 'refuse', false, false, false],
        ];
    }

    #[DataProvider('accessMatrix')]
    public function test_permissions_status_and_expiry(string $permission, string $status, bool $expired, bool $view, bool $write): void
    {
        $owner = $this->user();
        $recipient = $this->user();
        [$vault, $key] = $this->vault($owner);
        $element = $this->element($vault, $key);
        $this->share($owner, $recipient, $vault, $key, [
            'permission' => $permission, 'statut' => $status,
            'expire_le' => $expired ? now()->subMinute() : now()->addDay(),
        ]);
        $policy = new ElementCoffrePolicy;
        self::assertSame($view, $policy->view($recipient, $element));
        self::assertSame($write, $policy->update($recipient, $element));
        self::assertSame($write, $policy->delete($recipient, $element));
        self::assertTrue($policy->view($owner, $element));
        self::assertFalse($policy->view($this->user(), $element));
    }

    public function test_selected_share_cannot_read_an_unselected_secret_by_url(): void
    {
        $owner = $this->user();
        $recipient = $this->user();
        [$vault, $key] = $this->vault($owner);
        $selected = $this->element($vault, $key);
        $hidden = $this->element($vault, $key, ['mot_de_passe' => 'not-shared-secret']);
        [, $privateKey] = $this->share($owner, $recipient, $vault, $key, ['element_ids' => [$selected->id]]);
        $this->unlock($recipient, random_bytes(32), $privateKey);
        $this->get('/services/'.$selected->id)->assertOk();
        $this->get('/services/'.$hidden->id)->assertDontSee('not-shared-secret');
    }

    public function test_selected_share_cannot_modify_an_unselected_secret(): void
    {
        $owner = $this->user();
        $recipient = $this->user();
        [$vault, $key] = $this->vault($owner);
        $selected = $this->element($vault, $key);
        $hidden = $this->element($vault, $key, ['label' => 'Do not edit']);
        [, $privateKey] = $this->share($owner, $recipient, $vault, $key, [
            'element_ids' => [$selected->id], 'permission' => 'ecriture',
        ]);
        $this->unlock($recipient, random_bytes(32), $privateKey);
        $this->put('/services/'.$hidden->id, ['type' => 'login', 'label' => 'Unauthorized edit']);
        self::assertSame('Do not edit', $hidden->fresh()->label);
    }

    public function test_empty_share_selection_is_not_treated_as_the_entire_vault_by_api(): void
    {
        $owner = $this->user();
        $recipient = $this->user();
        [$vault, $key] = $this->vault($owner);
        $this->element($vault, $key);
        $this->share($owner, $recipient, $vault, $key, ['element_ids' => []]);
        // Controller-level isolation: the missing Sanctum alias has its own HTTP tests.
        $request = Request::create('/api/services');
        $request->setUserResolver(fn () => $recipient);
        $response = app(ServicesApiController::class)->index($request)->getData(true);
        self::assertSame(0, $response['total']);
    }

    public function test_api_controller_returns_only_selected_ciphertexts(): void
    {
        $owner = $this->user();
        $recipient = $this->user();
        [$vault, $key] = $this->vault($owner);
        $selected = $this->element($vault, $key);
        $this->element($vault, $key);
        $this->share($owner, $recipient, $vault, $key, ['element_ids' => [$selected->id]]);
        $request = Request::create('/api/services');
        $request->setUserResolver(fn () => $recipient);
        $response = app(ServicesApiController::class)->index($request)->getData(true);
        self::assertSame([$selected->id], array_column($response['services'], 'id'));
        self::assertStringNotContainsString('vault-secret-value', json_encode($response));
    }

    public function test_invitation_full_lifecycle_accepts_once_and_revokes_access(): void
    {
        [$owner, $ownerKeys] = $this->registeredUser();
        [$recipient, $recipientKeys] = $this->registeredUser();
        $vault = $owner->coffres()->firstOrFail();
        $key = app(CleManagementService::class)->dechiffrerDataKeyCoffre($vault->data_key_encrypted, $ownerKeys['kek']);
        $element = $this->element($vault, $key);
        $this->unlock($owner, $ownerKeys['kek'], $ownerKeys['cle_privee']);
        $this->post('/partage', [
            'coffre_id' => $vault->id, 'email' => $recipient->email,
            'permission' => 'lecture', 'element_ids' => [$element->id],
        ])->assertRedirect(route('partage.index'));
        $invitation = InvitationPartage::sole();
        $link = Mail::sent(InvitationPartageMail::class)->sole()->lienAcceptation;
        $token = basename(parse_url($link, PHP_URL_PATH));
        self::assertSame(hash('sha256', $token), $invitation->token_hash);
        $this->unlock($recipient, $recipientKeys['kek'], $recipientKeys['cle_privee']);
        $this->get($link)->assertRedirect(route('dashboard'));
        $this->get('/services/'.$element->id)->assertOk();
        $this->get($link)->assertNotFound();
        $this->assertDatabaseCount('shares_coffre', 1);
        $share = $recipient->sharesRecus()->sole();
        $this->unlock($owner, $ownerKeys['kek'], $ownerKeys['cle_privee']);
        $this->delete('/partage/'.$share->id)->assertRedirect();
        self::assertFalse((new ElementCoffrePolicy)->view($recipient, $element));
    }

    public function test_invitation_rejects_elements_from_a_different_vault(): void
    {
        $owner = $this->user();
        [$vault, , $kek] = $this->vault($owner);
        [$otherVault, $otherKey] = $this->vault($owner, $kek);
        $otherElement = $this->element($otherVault, $otherKey);
        $this->unlock($owner, $kek);
        $this->post('/partage', [
            'coffre_id' => $vault->id, 'email' => 'recipient@example.test',
            'permission' => 'lecture', 'element_ids' => [$otherElement->id],
        ])->assertSessionHasErrors('element_ids');
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }
}
