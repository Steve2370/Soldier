<?php

namespace Tests\Feature\Audit;

use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Stripe\ApiRequestor;
use Tests\Support\AuditTestCase;
use Tests\Support\FakeStripeHttpClient;

class PaymentFlowTest extends AuditTestCase
{
    private FakeStripeHttpClient $stripe;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.price_famille' => 'price_testFamily']);
        $this->stripe = new FakeStripeHttpClient;
        ApiRequestor::setHttpClient($this->stripe);
    }

    public function test_checkout_uses_configured_price_customer_and_family_metadata_without_granting_access(): void
    {
        $user = $this->user();
        $this->unlock($user, random_bytes(32));
        $this->stripe->responses = [
            ['method' => 'post', 'path' => '/v1/customers', 'body' => ['object' => 'customer', 'id' => 'cus_test']],
            ['method' => 'post', 'path' => '/v1/checkout/sessions', 'body' => ['object' => 'checkout.session', 'id' => 'cs_test', 'url' => 'https://checkout.stripe.com/c/pay/cs_test']],
        ];
        $this->post('/pricing/checkout')->assertRedirect('https://checkout.stripe.com/c/pay/cs_test')->assertStatus(303);
        $data = $this->stripe->requests[1]['params'];
        self::assertSame('subscription', $data['mode']);
        self::assertSame('cus_test', $data['customer']);
        self::assertSame('price_testFamily', $data['line_items'][0]['price']);
        self::assertSame('famille', $data['subscription_data']['metadata']['type']);
        self::assertSame(route('pricing'), $data['cancel_url']);
        self::assertSame(route('pricing.succes').'?session_id={CHECKOUT_SESSION_ID}', $data['success_url']);
        self::assertFalse($user->fresh()->hasFamilyPlan());
        $this->assertDatabaseCount('subscriptions', 0);
        self::assertSame([], $this->stripe->responses);
    }

    public static function missingConfiguration(): array
    {
        return [['services.stripe.price_famille'], ['cashier.secret'], ['cashier.webhook.secret']];
    }

    #[DataProvider('missingConfiguration')]
    public function test_missing_payment_configuration_fails_cleanly_without_fallback_price(string $key): void
    {
        config([$key => null]);
        $this->unlock($this->user(), random_bytes(32));
        $this->from('/pricing')->post('/pricing/checkout')->assertRedirect('/pricing')->assertSessionHasErrors('billing');
        // HTTP tests do not carry response cookies automatically like a browser.
        $this->withCookie(config('session.cookie'), session()->getId())->get('/pricing')
            ->assertOk()->assertSee('Le service de paiement est indisponible');
        self::assertSame([], $this->stripe->requests);
    }

    public function test_stripe_error_becomes_safe_message_not_a_500_or_secret_leak(): void
    {
        $this->unlock($this->user(['stripe_id' => 'cus_test']), random_bytes(32));
        $this->stripe->responses = [
            ['method' => 'get', 'path' => '/v1/customers/cus_test', 'status' => 401,
                'body' => ['error' => ['type' => 'authentication_error', 'message' => 'secret-provider-details']]],
        ];
        $this->postJson('/pricing/checkout')->assertUnprocessable()->assertJsonValidationErrors('billing')->assertDontSee('secret-provider-details');
    }

    public function test_return_url_is_not_a_proof_of_payment(): void
    {
        $user = $this->user();
        $this->unlock($user, random_bytes(32));
        $this->get('/pricing/succes?session_id=cs_someone_else')->assertOk()
            ->assertSee('Confirmation du paiement en attente')->assertDontSee('Votre accès Famille est actif');
        self::assertFalse($user->fresh()->hasFamilyPlan());
        self::assertSame([], $this->stripe->requests);
    }

    public function test_portal_targets_only_current_customer_and_incomplete_checkout_is_not_duplicated(): void
    {
        $user = $this->user(['stripe_id' => 'cus_test']);
        $user->subscriptions()->create(['type' => 'famille', 'stripe_id' => 'sub_test', 'stripe_status' => 'incomplete']);
        $this->unlock($user, random_bytes(32));
        $this->post('/pricing/checkout')->assertRedirect(route('pricing.portail'));
        $this->stripe->responses = [
            ['method' => 'post', 'path' => '/v1/billing_portal/sessions', 'body' => ['id' => 'bps_test', 'url' => 'https://billing.stripe.com/p/session/test']],
        ];
        $this->get('/pricing/portail')->assertRedirect('https://billing.stripe.com/p/session/test');
        self::assertSame(['customer' => 'cus_test', 'return_url' => route('pricing')], $this->stripe->requests[0]['params']);
        $this->assertDatabaseCount('subscriptions', 1);
    }

    private function event(string $type, string $id = 'sub_test', string $status = 'active'): array
    {
        return ['id' => 'evt_test', 'type' => $type, 'data' => ['object' => [
            'id' => $id, 'customer' => 'cus_test', 'status' => $status, 'metadata' => ['type' => 'famille'],
            'items' => ['data' => [['id' => 'si_test', 'price' => ['id' => 'price_testFamily', 'product' => 'prod_test'], 'quantity' => 1]]],
        ]]];
    }

    private function signed(array $payload, ?int $timestamp = null, string $secret = 'whsec_audit_not_a_real_secret'): TestResponse
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $timestamp ??= time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$json, $secret);

        return $this->call('POST', '/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ], $json);
    }

    public function test_signed_subscription_webhook_activates_plan_and_retries_do_not_duplicate(): void
    {
        $user = $this->user(['stripe_id' => 'cus_test']);
        $event = $this->event('customer.subscription.created');
        $this->signed($event)->assertOk();
        $this->signed($event)->assertOk();
        self::assertTrue($user->fresh()->hasFamilyPlan());
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('subscription_items', 1);
        $this->unlock($user->fresh(), random_bytes(32));
        $this->get('/pricing/succes')->assertSee('Votre accès Famille est actif');
    }

    public function test_webhooks_reject_wrong_or_expired_signatures_and_missing_secret(): void
    {
        $this->user(['stripe_id' => 'cus_test']);
        $event = $this->event('customer.subscription.created');
        $this->signed($event, secret: 'wrong')->assertForbidden();
        $this->signed($event, time() - 600)->assertForbidden();
        $this->postJson('/stripe/webhook', $event)->assertForbidden();
        config(['cashier.webhook.secret' => null]);
        $this->postJson('/stripe/webhook', $event)->assertStatus(503);
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public static function lossCases(): array
    {
        return [
            [false, 'sub_test', false, 'customer.subscription.deleted', 'canceled'],
            [true, 'sub_test', true, 'customer.subscription.deleted', 'canceled'],
            [false, 'sub_unrelated', true, 'customer.subscription.deleted', 'canceled'],
            [false, 'sub_test', false, 'customer.subscription.updated', 'unpaid'],
            [true, 'sub_test', true, 'customer.subscription.updated', 'unpaid'],
            [false, 'sub_test', false, 'customer.subscription.updated', 'past_due'],
            [false, 'sub_test', true, 'customer.subscription.updated', 'active'],
        ];
    }

    #[DataProvider('lossCases')]
    public function test_signed_cancellation_preserves_gifts_and_ignores_other_products(bool $gift, string $subId, bool $kept, string $type, string $status): void
    {
        $user = $this->user(['stripe_id' => 'cus_test', 'family_granted_at' => $gift ? now() : null]);
        $user->subscriptions()->create(['type' => 'famille', 'stripe_id' => 'sub_test', 'stripe_status' => 'active']);
        $user->subscriptions()->create(['type' => 'other', 'stripe_id' => 'sub_unrelated', 'stripe_status' => 'active']);
        [$vault, $key] = $this->vault($user);
        $group = FamilyGroup::create(['owner_id' => $user->id, 'nom' => 'Webhook test', 'coffre_id' => $vault->id]);
        $member = $this->user();
        $membership = FamilyMember::create(['family_group_id' => $group->id, 'user_id' => $member->id, 'role' => 'member']);
        [$share] = $this->share($user, $member, $vault, $key, ['family_group_id' => $group->id]);
        $event = $this->event($type, $subId, $status);
        $this->signed($event)->assertOk();
        $this->signed($event)->assertOk();
        self::assertSame($kept, FamilyMember::whereKey($membership->id)->exists());
        self::assertSame($kept, $share->newQuery()->whereKey($share->id)->exists());
        self::assertSame($kept, $user->fresh()->hasFamilyPlan());
    }

    public function test_ending_an_old_subscription_preserves_another_valid_family_subscription(): void
    {
        $user = $this->user(['stripe_id' => 'cus_test']);
        foreach (['sub_old', 'sub_test'] as $id) {
            $user->subscriptions()->create(['type' => 'famille', 'stripe_id' => $id, 'stripe_status' => 'active']);
        }
        $group = FamilyGroup::create(['owner_id' => $user->id, 'nom' => 'Retained family']);
        $membership = FamilyMember::create(['family_group_id' => $group->id, 'user_id' => $this->user()->id, 'role' => 'member']);
        $this->signed($this->event('customer.subscription.deleted', 'sub_old', 'canceled'))->assertOk();
        $this->assertDatabaseHas('family_members', ['id' => $membership->id]);
        self::assertTrue($user->fresh()->hasFamilyPlan());
    }

    public function test_stripe_portal_failure_is_safe(): void
    {
        $this->unlock($this->user(['stripe_id' => 'cus_test']), random_bytes(32));
        $this->stripe->responses = [
            ['method' => 'post', 'path' => '/v1/billing_portal/sessions', 'status' => 400,
                'body' => ['error' => ['type' => 'invalid_request_error', 'message' => 'private-provider-details']]],
        ];
        $this->getJson('/pricing/portail')->assertUnprocessable()->assertJsonValidationErrors('billing')->assertDontSee('private-provider-details');
    }

    public function test_coffee_links_are_explicit_safe_external_paypal_links(): void
    {
        $this->get('/pricing')->assertOk()->assertSee('href="https://paypal.me/BriceSteve" target="_blank" rel="noopener noreferrer"', false);
        $this->unlock($this->user(), random_bytes(32));
        $this->get('/dashboard')->assertOk()->assertSee('href="https://paypal.me/BriceSteve" target="_blank" rel="noopener noreferrer"', false);
    }
}
