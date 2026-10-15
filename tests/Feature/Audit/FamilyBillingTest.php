<?php

namespace Tests\Feature\Audit;

use App\Listeners\StripeEventListener;
use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Policies\ElementCoffrePolicy;
use Laravel\Cashier\Events\WebhookReceived;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacySchemaAuditTestCase;

class FamilyBillingTest extends LegacySchemaAuditTestCase
{
    private function family(): array
    {
        $owner = $this->user(['stripe_id' => 'cus_audit']);
        $owner->subscriptions()->create([
            'type' => 'famille', 'stripe_id' => 'sub_audit', 'stripe_status' => 'active', 'stripe_price' => 'price_audit',
        ]);
        $member = $this->user();
        [$vault, $key, $kek] = $this->vault($owner);
        $group = FamilyGroup::create(['owner_id' => $owner->id, 'nom' => 'Audit Family', 'coffre_id' => $vault->id]);
        FamilyMember::create(['family_group_id' => $group->id, 'user_id' => $owner->id, 'role' => 'owner']);
        $membership = FamilyMember::create(['family_group_id' => $group->id, 'user_id' => $member->id, 'role' => 'member']);
        $element = $this->element($vault, $key);
        [, $privateKey] = $this->share($owner, $member, $vault, $key, ['permission' => 'ecriture', 'expire_le' => null]);

        return compact('owner', 'member', 'vault', 'key', 'kek', 'group', 'membership', 'element', 'privateKey');
    }

    public function test_family_page_renders_for_subscriber_and_member(): void
    {
        $f = $this->family();
        $this->unlock($f['owner'], $f['kek']);
        $this->get('/famille')->assertOk()->assertSee('Service audit');
        $this->unlock($f['member'], random_bytes(32), $f['privateKey']);
        $this->get('/famille')->assertOk()->assertSee('Service audit');
    }

    public function test_removing_member_revokes_family_vault_access(): void
    {
        $f = $this->family();
        $this->unlock($f['owner'], $f['kek']);
        $this->delete('/famille/membres/'.$f['membership']->id)->assertRedirect();
        $this->assertDatabaseMissing('family_members', ['id' => $f['membership']->id]);
        self::assertFalse((new ElementCoffrePolicy)->view($f['member'], $f['element']));
    }

    public function test_leaving_family_revokes_vault_access(): void
    {
        $f = $this->family();
        $this->unlock($f['member'], random_bytes(32), $f['privateKey']);
        $this->delete('/famille/quitter')->assertRedirect();
        $this->assertDatabaseMissing('family_members', ['id' => $f['membership']->id]);
        self::assertFalse((new ElementCoffrePolicy)->update($f['member'], $f['element']));
    }

    public static function subscriptionFailures(): array
    {
        return [['customer.subscription.deleted', 'canceled'], ['customer.subscription.updated', 'unpaid']];
    }

    #[DataProvider('subscriptionFailures')]
    public function test_subscription_loss_revokes_family_shares(string $type, string $status): void
    {
        $f = $this->family();
        app(StripeEventListener::class)->handle(new WebhookReceived([
            'type' => $type, 'data' => ['object' => ['id' => 'sub_audit', 'customer' => 'cus_audit', 'status' => $status]],
        ]));
        $this->assertDatabaseMissing('family_members', ['id' => $f['membership']->id]);
        self::assertFalse((new ElementCoffrePolicy)->view($f['member'], $f['element']));
    }

    public function test_non_subscriber_cannot_create_a_family(): void
    {
        $this->unlock($this->user(), random_bytes(32));
        $this->post('/famille/creer')->assertRedirect(route('pricing'));
        $this->assertDatabaseCount('family_groups', 0);
    }

    public function test_member_cannot_remove_another_family_member(): void
    {
        $f = $this->family();
        $this->unlock($f['member'], random_bytes(32), $f['privateKey']);
        $this->delete('/famille/membres/'.$f['membership']->id)->assertNotFound();
        $this->assertDatabaseHas('family_members', ['id' => $f['membership']->id]);
    }

    public function test_unsigned_stripe_webhook_is_rejected(): void
    {
        $this->postJson('/stripe/webhook', ['type' => 'customer.subscription.deleted'])->assertForbidden();
    }

    public function test_already_subscribed_checkout_redirects_without_creating_another_subscription(): void
    {
        $f = $this->family();
        $this->unlock($f['owner'], $f['kek']);
        $this->post('/pricing/checkout')->assertRedirect(route('pricing.portail'));
        $this->assertDatabaseCount('subscriptions', 1);
    }
}
