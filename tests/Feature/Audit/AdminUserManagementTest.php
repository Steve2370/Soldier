<?php

namespace Tests\Feature\Audit;

use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\User;
use App\Services\Admin\DeleteUserService;
use App\Services\Admin\RemoveUserAvatar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacySchemaAuditTestCase;

class AdminUserManagementTest extends LegacySchemaAuditTestCase
{
    public function test_admin_sees_actions_but_cannot_delete_self_from_ui(): void
    {
        $admin = $this->user(['is_admin' => true]);
        $target = $this->user(['name' => '<img src=x onerror=alert(1)>']);
        $this->actingAs($admin)->get('/admin?tab=users')->assertOk()
            ->assertSee(route('admin.users.supprimer', $target), false)
            ->assertSee('confirmation_email')->assertSee('Offrir le forfait Famille')
            ->assertDontSee('action="'.route('admin.users.supprimer', $admin).'"', false)
            ->assertDontSee($target->name, false)->assertSee(e($target->name), false);
    }

    public function test_guests_and_non_admins_cannot_mutate_accounts(): void
    {
        $target = $this->user();
        $this->deleteJson('/admin/users/'.$target->id)->assertUnauthorized();
        $this->putJson('/admin/users/'.$target->id.'/family-grant')->assertUnauthorized();
        $this->actingAs($this->user());
        $this->deleteJson('/admin/users/'.$target->id, ['confirmation_email' => $target->email])->assertForbidden();
        $this->putJson('/admin/users/'.$target->id.'/family-grant')->assertForbidden();
        $this->deleteJson('/admin/users/'.$target->id.'/family-grant', ['confirmation' => true])->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $target->id, 'family_granted_at' => null]);
    }

    public function test_confirmation_and_self_deletion_are_enforced_by_server(): void
    {
        $admin = $this->user(['is_admin' => true]);
        $target = $this->user();
        $this->actingAs($admin);
        foreach (['', 'wrong@example.test'] as $email) {
            $this->deleteJson('/admin/users/'.$target->id, ['confirmation_email' => $email])
                ->assertUnprocessable()->assertJsonValidationErrors('confirmation_email');
        }
        $this->deleteJson('/admin/users/'.$admin->id, ['confirmation_email' => $admin->email])
            ->assertUnprocessable()->assertJsonValidationErrors('confirmation_email');
        $this->assertDatabaseCount('users', 2);
        $this->deleteJson('/admin/users/999999', ['confirmation_email' => $target->email])->assertNotFound();
    }

    public static function billedStatuses(): array
    {
        return array_map(fn ($status) => [$status], ['active', 'trialing', 'past_due', 'unpaid', 'incomplete', 'paused']);
    }

    #[DataProvider('billedStatuses')]
    public function test_deletion_never_orphans_an_ongoing_stripe_subscription(string $status): void
    {
        $user = $this->user();
        $user->subscriptions()->create(['type' => 'famille', 'stripe_id' => 'sub_test', 'stripe_status' => $status]);
        $this->actingAs($this->user(['is_admin' => true]))
            ->deleteJson('/admin/users/'.$user->id, ['confirmation_email' => $user->email])
            ->assertUnprocessable()->assertJsonValidationErrors('confirmation_email');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('subscriptions', ['stripe_id' => 'sub_test', 'stripe_status' => $status]);
    }

    public function test_canceled_subscription_grace_period_must_end_before_deletion(): void
    {
        $user = $this->user();
        $sub = $user->subscriptions()->create(['type' => 'famille', 'stripe_id' => 'sub_test', 'stripe_status' => 'canceled', 'ends_at' => now()->addDay()]);
        $this->actingAs($this->user(['is_admin' => true]));
        $this->deleteJson('/admin/users/'.$user->id, ['confirmation_email' => $user->email])->assertUnprocessable();
        $sub->update(['ends_at' => now()->subMinute()]);
        $this->delete('/admin/users/'.$user->id, ['confirmation_email' => $user->email])->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_confirmed_deletion_cascades_private_data_and_preserves_other_users(): void
    {
        Storage::fake('public');
        $admin = $this->user(['is_admin' => true]);
        [$target] = $this->registeredUser(['avatar' => 'avatars/test-avatar.png']);
        Storage::disk('public')->put('avatars/test-avatar.png', 'test-only');
        $other = $this->user();
        [$vault, $key] = $this->vault($target);
        $element = $this->element($vault, $key);
        [$share] = $this->share($target, $other, $vault, $key);
        [$otherVault, $otherKey] = $this->vault($other);
        $kept = $this->element($otherVault, $otherKey);
        $this->share($other, $target, $otherVault, $otherKey);
        $group = FamilyGroup::create(['owner_id' => $target->id, 'nom' => 'To delete', 'coffre_id' => $vault->id]);
        FamilyMember::create(['family_group_id' => $group->id, 'user_id' => $other->id, 'role' => 'member']);
        $target->createToken('test-only');
        $target->mfa()->create(['type' => 'email']);
        $target->passKeys()->create(['nom' => 'Test', 'credential_id' => 'test-credential', 'cle_publique' => 'test-only']);
        DB::table('sessions')->insert(['id' => 'target-session', 'user_id' => $target->id, 'payload' => 'test', 'last_activity' => time()]);
        DB::table('password_reset_tokens')->insert(['email' => $target->email, 'token' => 'test-only']);
        $vault->delete(); // A previously trashed vault must also be purged.
        $this->actingAs($admin)->delete('/admin/users/'.$target->id, ['confirmation_email' => $target->email])->assertRedirect();
        foreach (['users' => $target->id, 'coffres' => $vault->id, 'elements_coffres' => $element->id,
            'shares_coffre' => $share->id, 'family_groups' => $group->id] as $table => $id) {
            $this->assertDatabaseMissing($table, ['id' => $id]);
        }
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseMissing('sessions', ['id' => 'target-session']);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $target->email]);
        $this->assertDatabaseCount('mfa', 0);
        $this->assertDatabaseCount('passkeys', 0);
        $this->assertDatabaseMissing('cles_user', ['user_id' => $target->id]);
        $this->assertDatabaseMissing('coffres', ['user_id' => $target->id]);
        Storage::disk('public')->assertMissing('avatars/test-avatar.png');
        $this->assertDatabaseHas('users', ['id' => $other->id]);
        $this->assertDatabaseHas('elements_coffres', ['id' => $kept->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'user_supprime', 'user_id' => $admin->id]);
    }

    public function test_failed_deletion_rolls_back_session_and_token_cleanup(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/retained.png', 'test-only');
        $admin = $this->user(['is_admin' => true]);
        $target = $this->user(['avatar' => 'avatars/retained.png']);
        $target->createToken('retained');
        DB::table('sessions')->insert(['id' => 'retained', 'user_id' => $target->id, 'payload' => 'test', 'last_activity' => time()]);
        Event::listen('eloquent.deleting: '.User::class, fn () => throw new \RuntimeException('Simulated deletion failure'));
        try {
            app(DeleteUserService::class)->delete($admin, $target);
            self::fail('Expected simulated failure');
        } catch (\RuntimeException $error) {
            self::assertSame('Simulated deletion failure', $error->getMessage());
        } finally {
            Event::forget('eloquent.deleting: '.User::class);
        }
        $this->assertDatabaseHas('users', ['id' => $target->id]);
        $this->assertDatabaseHas('sessions', ['id' => 'retained']);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        Storage::disk('public')->assertExists('avatars/retained.png');
    }

    public function test_avatar_cleanup_never_removes_shared_or_arbitrary_files(): void
    {
        Storage::fake('public');
        foreach (['avatars/shared.png', 'documents/retained.png'] as $path) {
            Storage::disk('public')->put($path, 'test-only');
        }
        $this->user(['avatar' => 'avatars/shared.png']);
        $cleanup = app(RemoveUserAvatar::class);
        $cleanup->remove('avatars/shared.png');
        $cleanup->remove('documents/retained.png');
        $cleanup->remove('avatars/../documents/retained.png');
        Storage::disk('public')->assertExists('avatars/shared.png');
        Storage::disk('public')->assertExists('documents/retained.png');
    }
}
