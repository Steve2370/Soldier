<?php

namespace Tests\Feature;

use App\Mail\BienvenueMail;
use App\Notifications\VerificationEmailNotification;
use App\Services\Auth\Contracts\UserRegistrationNotificationInterface;
use App\Services\Crypto\Contracts\CryptoAsymmetricInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_user_keys_and_vault(): void
    {
        Mail::fake();
        Notification::fake();

        $response = $this->post(route('inscription.post'), [
            'name' => 'Utilisateur Test',
            'email' => 'inscription@example.test',
            'password' => 'account-password',
            'password_confirmation' => 'account-password',
            'master_password' => 'master-password-secure',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'inscription@example.test']);

        $userId = (int) \DB::table('users')
            ->where('email', 'inscription@example.test')
            ->value('id');

        $this->assertDatabaseHas('cles_user', ['user_id' => $userId]);
        $this->assertDatabaseHas('coffres', ['user_id' => $userId, 'nom' => 'Mon coffre']);
        Notification::assertSentTo($this->app['auth']->user(), VerificationEmailNotification::class);
        Mail::assertSent(BienvenueMail::class);
    }

    public function test_crypto_failure_rolls_back_the_entire_registration(): void
    {
        $asymmetric = Mockery::mock(CryptoAsymmetricInterface::class);
        $asymmetric->shouldReceive('genererPaireCles')
            ->once()
            ->andThrow(new RuntimeException('crypto unavailable'));

        $this->app->instance(CryptoAsymmetricInterface::class, $asymmetric);
        $this->app->forgetInstance(\App\Services\Coffre\CleManagementService::class);
        $this->app->forgetInstance(\App\Services\Coffre\CoffreService::class);

        $response = $this->from(route('inscription'))
            ->post(route('inscription.post'), [
                'name' => 'Utilisateur Rollback',
                'email' => 'rollback@example.test',
                'password' => 'account-password',
                'password_confirmation' => 'account-password',
                'master_password' => 'master-password-secure',
            ]);

        $response->assertRedirect(route('inscription'));
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'rollback@example.test']);
        $this->assertDatabaseCount('cles_user', 0);
        $this->assertDatabaseCount('coffres', 0);
    }

    public function test_email_provider_failure_does_not_turn_a_successful_registration_into_a_500(): void
    {
        $notifications = Mockery::mock(UserRegistrationNotificationInterface::class);
        $notifications->shouldReceive('envoyer')->once()->andReturnFalse();
        $this->app->instance(UserRegistrationNotificationInterface::class, $notifications);

        $response = $this->post(route('inscription.post'), [
            'name' => 'Utilisateur Email',
            'email' => 'email-failure@example.test',
            'password' => 'account-password',
            'password_confirmation' => 'account-password',
            'master_password' => 'master-password-secure',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'email-failure@example.test']);
    }
}
