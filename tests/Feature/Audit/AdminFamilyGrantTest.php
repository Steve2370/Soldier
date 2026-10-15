<?php

namespace Tests\Feature\Audit;

use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Services\Admin\FamilyGrantService;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\AuditTestCase;

class AdminFamilyGrantTest extends AuditTestCase
{
    public function test_grant_is_persisted_idempotent_and_cannot_be_mass_assigned(): void
    {
        $user = $this->user();
        $user->fill(['family_granted_at' => now()])->save();
        self::assertNull($user->fresh()->family_granted_at);
        $admin = $this->user(['is_admin' => true]);
        $this->actingAs($admin);
        for ($i = 0; $i < 2; $i++) {
            $this->put('/admin/users/'.$user->id.'/family-grant')->assertRedirect();
        }
        self::assertTrue($user->fresh()->hasFamilyPlan());
        self::assertFalse($user->fresh()->subscribed('famille'));
        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $admin->id, 'action' => 'famille_offerte']);
        $this->assertDatabaseCount('activity_logs', 1);
        $this->unlock($user->fresh(), random_bytes(32));
        $this->get('/famille')->assertOk()->assertSee(route('famille.creer'), false);
        $this->get('/pricing')->assertSee('Votre accès Famille est offert')->assertDontSee('Gérer mon abonnement');
        $this->post('/pricing/checkout')->assertRedirect(route('famille.index'));
        $this->get('/pricing/portail')->assertRedirect(route('pricing'));
    }

    public static function paidCases(): array
    {
        return [[false], [true]];
    }

    #[DataProvider('paidCases')]
    public function test_revoking_gift_only_revokes_family_shares_if_no_paid_plan_remains(bool $paid): void
    {
        $admin = $this->user(['is_admin' => true]);
        $owner = $this->user(['family_granted_at' => now()]);
        $member = $this->user();
        if ($paid) {
            $owner->subscriptions()->create(['type' => 'famille', 'stripe_id' => 'sub_paid', 'stripe_status' => 'active']);
        }
        [$vault, $key, $kek] = $this->vault($owner);
        $group = FamilyGroup::create(['owner_id' => $owner->id, 'nom' => 'Granted family', 'coffre_id' => $vault->id]);
        FamilyMember::create(['family_group_id' => $group->id, 'user_id' => $owner->id, 'role' => 'owner']);
        $membership = FamilyMember::create(['family_group_id' => $group->id, 'user_id' => $member->id, 'role' => 'member']);
        [$share, $private] = $this->share($owner, $member, $vault, $key, ['family_group_id' => $group->id]);
        [$independentVault, $independentKey] = $this->vault($owner);
        [$independent] = $this->share($owner, $member, $independentVault, $independentKey);
        $this->unlock($member, random_bytes(32), $private);
        $this->get('/famille')->assertOk();
        $this->actingAs($admin)->deleteJson('/admin/users/'.$owner->id.'/family-grant')->assertUnprocessable();
        self::assertNotNull($owner->fresh()->family_granted_at);
        $this->delete('/admin/users/'.$owner->id.'/family-grant', ['confirmation' => '1'])->assertRedirect();
        self::assertNull($owner->fresh()->family_granted_at);
        self::assertSame($paid, $owner->fresh()->hasFamilyPlan());
        self::assertSame($paid, FamilyMember::whereKey($membership->id)->exists());
        self::assertSame($paid, $share->newQuery()->whereKey($share->id)->exists());
        $this->assertDatabaseHas('shares_coffre', ['id' => $independent->id]);
        $this->assertDatabaseHas('coffres', ['id' => $vault->id]);
        if ($paid) {
            $this->assertDatabaseHas('subscriptions', ['stripe_id' => 'sub_paid', 'stripe_status' => 'active']);
        }
        $this->unlock($owner->fresh(), $kek);
        if ($paid) {
            $this->get('/famille')->assertOk();
        } else {
            $this->get('/famille')->assertRedirect(route('pricing'));
        }
    }

    public function test_service_also_rejects_non_admin_and_unknown_targets(): void
    {
        $this->actingAs($this->user(['is_admin' => true]))->putJson('/admin/users/999999/family-grant')->assertNotFound();
        $this->expectException(HttpException::class);
        app(FamilyGrantService::class)->setGranted($this->user(), $this->user(), true);
    }
}
