<?php

namespace Tests\Feature\Audit;

use App\Mail\InvitationPartageMail;
use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\InvitationPartage;
use App\Services\Crypto\Contracts\CryptoAsymmetricInterface;
use App\Services\Notifications\Contracts\MailDeliveryInterface;
use App\Services\Partage\SendShareInvitations;
use Illuminate\Support\Facades\Mail;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacySchemaAuditTestCase;

class SharingAudienceTest extends LegacySchemaAuditTestCase
{
    private function sender(bool $entitled = true): array
    {
        $owner = $this->user();
        if ($entitled) {
            $owner->forceFill(['family_granted_at' => now()])->save();
        }
        [$vault, $key, $kek] = $this->vault($owner);
        $element = $this->element($vault, $key);
        $this->element($vault, $key, ['label' => 'Not selected']);
        [$familyVault] = $this->vault($owner, $kek);
        $group = FamilyGroup::create(['owner_id' => $owner->id, 'nom' => 'Audience test', 'coffre_id' => $familyVault->id]);
        FamilyMember::create(['family_group_id' => $group->id, 'user_id' => $owner->id, 'role' => 'owner']);
        $this->unlock($owner, $kek);
        $payload = ['coffre_id' => $vault->id, 'element_ids' => [$element->id], 'permission' => 'lecture'];

        return compact('owner', 'vault', 'key', 'kek', 'group', 'payload');
    }

    private function member(FamilyGroup $group): array
    {
        [$user, $keys] = $this->registeredUser();
        FamilyMember::create(['family_group_id' => $group->id, 'user_id' => $user->id, 'role' => 'member']);

        return [$user, $keys];
    }

    public function test_individual_mode_targets_only_the_chosen_person_even_with_a_family(): void
    {
        $f = $this->sender();
        [$member] = $this->member($f['group']);
        [$recipient, $keys] = $this->registeredUser();
        $this->post('/partage', $f['payload'] + ['partage_groupe' => '0', 'email' => strtoupper($recipient->email)])
            ->assertRedirect(route('partage.index'))->assertSessionHasNoErrors();
        $invitation = InvitationPartage::sole();
        self::assertSame($recipient->email, $invitation->email_destinataire);
        self::assertNull($invitation->family_group_id);
        self::assertSame($f['payload']['element_ids'], $invitation->element_ids);
        self::assertSame($f['key'], app(CryptoAsymmetricInterface::class)->decrypterAvecClePrivee($invitation->data_key_encrypted, $keys['cle_privee']));
        Mail::assertSent(InvitationPartageMail::class, 1);
        Mail::assertSent(InvitationPartageMail::class, fn ($mail) => $mail->hasTo($recipient->email) && ! $mail->hasTo($member->email));
    }

    public static function familyPlans(): array
    {
        return ['offered' => [false], 'paid' => [true]];
    }

    #[DataProvider('familyPlans')]
    public function test_family_mode_targets_only_its_members_and_encrypts_for_each_recipient(bool $paid): void
    {
        $f = $this->sender(! $paid);
        if ($paid) {
            $f['owner']->subscriptions()->create(['type' => 'famille', 'stripe_id' => 'sub_audience', 'stripe_status' => 'active', 'stripe_price' => 'price_test']);
        }
        $members = [$this->member($f['group']), $this->member($f['group'])];
        [$outsider] = $this->registeredUser();
        $otherGroup = FamilyGroup::create(['owner_id' => $outsider->id, 'nom' => 'Other family', 'coffre_id' => $outsider->coffres()->first()->id]);
        $this->post('/partage', $f['payload'] + ['partage_groupe' => '1', 'email' => 'invalid-stale-email', 'family_group_id' => $otherGroup->id])
            ->assertRedirect(route('partage.index'))->assertSessionHasNoErrors();
        $invitations = InvitationPartage::all();
        self::assertEqualsCanonicalizing(array_map(fn ($member) => $member[0]->email, $members), $invitations->pluck('email_destinataire')->all());
        foreach ($members as [$member, $keys]) {
            $invitation = $invitations->firstWhere('email_destinataire', $member->email);
            self::assertSame($f['group']->id, $invitation->family_group_id);
            self::assertSame($f['payload']['element_ids'], $invitation->element_ids);
            self::assertSame($f['key'], app(CryptoAsymmetricInterface::class)->decrypterAvecClePrivee($invitation->data_key_encrypted, $keys['cle_privee']));
            Mail::assertSent(InvitationPartageMail::class, fn ($mail) => $mail->hasTo($member->email));
            $link = Mail::sent(InvitationPartageMail::class)->first(fn ($mail) => $mail->hasTo($member->email))->lienAcceptation;
            $this->unlock($member, $keys['kek'], $keys['cle_privee']);
            $this->get($link)->assertRedirect(route('dashboard'));
            $share = $member->sharesRecus()->sole();
            self::assertSame($f['payload']['element_ids'], $share->element_ids);
            self::assertSame($f['group']->id, $share->family_group_id);
        }
        Mail::assertSent(InvitationPartageMail::class, 2);
    }

    public function test_individual_share_to_one_family_member_does_not_include_the_others(): void
    {
        $f = $this->sender();
        [$chosen] = $this->member($f['group']);
        $this->member($f['group']);
        $this->post('/partage', $f['payload'] + ['partage_groupe' => '0', 'email' => $chosen->email])->assertSessionHasNoErrors();
        self::assertSame($chosen->email, InvitationPartage::sole()->email_destinataire);
        self::assertNull(InvitationPartage::sole()->family_group_id);
        Mail::assertSent(InvitationPartageMail::class, 1);
    }

    public static function invalidModes(): array
    {
        return [['2'], ['famille'], [['0', '1']], [null]];
    }

    #[DataProvider('invalidModes')]
    public function test_invalid_mode_cannot_fall_back_to_another_audience(mixed $mode): void
    {
        $f = $this->sender();
        $this->from('/partage')->post('/partage', $f['payload'] + ['partage_groupe' => $mode, 'email' => 'recipient@example.test'])
            ->assertSessionHasErrors('partage_groupe');
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }

    public function test_family_mode_requires_an_active_paid_or_offered_plan(): void
    {
        $f = $this->sender(false);
        $this->member($f['group']);
        $this->postJson('/partage', $f['payload'] + ['partage_groupe' => '1'])->assertForbidden();
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }

    public function test_empty_family_reports_an_error_not_a_successful_send(): void
    {
        $f = $this->sender();
        $this->from('/partage')->post('/partage', $f['payload'] + ['partage_groupe' => '1'])
            ->assertSessionHasErrors('partage_groupe');
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }

    public function test_member_cannot_use_another_owners_group_as_the_sender(): void
    {
        $f = $this->sender();
        $f['group']->update(['owner_id' => $this->user()->id]);
        $this->from('/partage')->post('/partage', $f['payload'] + ['partage_groupe' => '1', 'family_group_id' => $f['group']->id])
            ->assertSessionHasErrors('partage_groupe');
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }

    public function test_uppercase_self_address_is_rejected_and_selection_is_retained(): void
    {
        $f = $this->sender();
        $this->from('/partage')->post('/partage', $f['payload'] + ['partage_groupe' => '0', 'email' => strtoupper($f['owner']->email)])
            ->assertSessionHasErrors('email')->assertSessionHasInput('element_ids', $f['payload']['element_ids']);
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }

    public function test_another_owners_vault_cannot_be_shared(): void
    {
        $f = $this->sender();
        [$vault, $key] = $this->vault($this->user());
        $element = $this->element($vault, $key);
        $this->postJson('/partage', ['coffre_id' => $vault->id, 'element_ids' => [$element->id], 'permission' => 'lecture', 'partage_groupe' => '0', 'email' => 'recipient@example.test'])
            ->assertForbidden();
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }

    public static function unreadyRecipients(): array
    {
        return ['unknown account' => [false], 'uninitialized account' => [true]];
    }

    #[DataProvider('unreadyRecipients')]
    public function test_an_unusable_invitation_is_not_created_for_an_unready_recipient(bool $exists): void
    {
        $f = $this->sender();
        if ($exists) {
            $this->user(['email' => 'unready@example.test']);
        }
        $this->from('/partage')->post('/partage', $f['payload'] + ['email' => 'unready@example.test'])
            ->assertSessionHasErrors('email')->assertSessionHasInput('element_ids', $f['payload']['element_ids']);
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }

    public function test_a_family_member_without_keys_prevents_a_partial_batch(): void
    {
        $f = $this->sender();
        $this->member($f['group']);
        FamilyMember::create(['family_group_id' => $f['group']->id, 'user_id' => $this->user()->id, 'role' => 'member']);
        $this->from('/partage')->post('/partage', $f['payload'] + ['partage_groupe' => '1'])
            ->assertSessionHasErrors('partage_groupe');
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }

    public function test_no_invitation_is_created_when_all_family_members_already_have_access(): void
    {
        $f = $this->sender();
        [$member] = $this->member($f['group']);
        $this->share($f['owner'], $member, $f['vault'], $f['key']);
        $this->from('/partage')->post('/partage', $f['payload'] + ['partage_groupe' => '1'])
            ->assertSessionHasErrors('partage_groupe');
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }

    public function test_existing_access_is_preserved_and_only_the_remaining_member_is_invited(): void
    {
        $f = $this->sender();
        [$existing] = $this->member($f['group']);
        [$remaining] = $this->member($f['group']);
        [$share] = $this->share($f['owner'], $existing, $f['vault'], $f['key']);
        $before = $share->fresh()->getAttributes();
        $this->post('/partage', $f['payload'] + ['partage_groupe' => '1'])->assertSessionHasNoErrors();
        self::assertSame($remaining->email, InvitationPartage::sole()->email_destinataire);
        self::assertSame($before, $share->fresh()->getAttributes());
        Mail::assertSent(InvitationPartageMail::class, 1);
    }

    public function test_family_encryption_failure_rolls_back_the_entire_batch_without_email(): void
    {
        $f = $this->sender();
        $this->member($f['group']);
        $this->member($f['group']);
        $attempt = 0;
        $this->instance(CryptoAsymmetricInterface::class, Mockery::mock(CryptoAsymmetricInterface::class)
            ->shouldReceive('chiffrerAvecClePublique')->twice()->andReturnUsing(function () use (&$attempt): string {
                if (++$attempt === 2) {
                    throw new \RuntimeException('Synthetic encryption failure');
                }

                return 'synthetic-first-envelope';
            })->getMock());
        try {
            app(SendShareInvitations::class)->send($f['owner'], $f['payload'] + ['partage_groupe' => '1'], $f['kek']);
            self::fail('The simulated encryption error must propagate.');
        } catch (\RuntimeException $error) {
            self::assertSame('Synthetic encryption failure', $error->getMessage());
        }
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }

    public function test_partial_mail_failure_reports_a_warning_and_preserves_both_invitations(): void
    {
        $f = $this->sender();
        $this->member($f['group']);
        $this->member($f['group']);
        $this->instance(MailDeliveryInterface::class, Mockery::mock(MailDeliveryInterface::class)
            ->shouldReceive('send')->twice()->andReturn(true, false)->getMock());
        $this->post('/partage', $f['payload'] + ['partage_groupe' => '1'])
            ->assertSessionHasNoErrors()->assertSessionHas('toast.type', 'warning')
            ->assertSessionHas('toast.message', fn ($message) => str_contains($message, '2 invitation(s) créée(s), 1 email(s) envoyé(s).'));
        $this->assertDatabaseCount('invitations_partage', 2);
    }

    public function test_family_mode_cannot_include_an_element_from_another_vault(): void
    {
        $f = $this->sender();
        [$other, $key] = $this->vault($f['owner'], $f['kek']);
        $element = $this->element($other, $key);
        $payload = array_replace($f['payload'], ['partage_groupe' => '1', 'element_ids' => [$element->id]]);
        $this->from('/partage')->post('/partage', $payload)->assertSessionHasErrors('element_ids');
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }

    public function test_family_vault_cannot_issue_an_unusable_invitation_to_an_outsider(): void
    {
        $f = $this->sender();
        [$recipient] = $this->registeredUser();
        $f['group']->update(['coffre_id' => $f['vault']->id]);
        $this->from('/partage')->post('/partage', $f['payload'] + ['partage_groupe' => '0', 'email' => $recipient->email])
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('invitations_partage', 0);
        Mail::assertNothingSent();
    }
}
