<?php

namespace Tests\Support;

use App\Models\Coffre;
use App\Models\ElementCoffre;
use App\Models\ShareCoffre;
use App\Models\User;
use App\Services\Coffre\CleManagementService;
use App\Services\Coffre\CoffreService;
use App\Services\Crypto\RsaCryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

abstract class AuditTestCase extends TestCase
{
    use RefreshDatabase;

    protected const MASTER = 'audit-master-password-2026';

    protected const PASSWORD = 'audit-account-password-2026';

    public function createApplication()
    {
        $app = parent::createApplication();
        if (! $app->environment('testing')
            || $app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('Audit refused: use APP_ENV=testing and SQLite :memory:, without cached configuration.');
        }
        $app['config']->set([
            'app.url' => 'http://localhost',
            'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
            'session.driver' => 'array',
            'cache.default' => 'array',
            'mail.default' => 'array',
            'logging.default' => 'null',
            'cashier.secret' => 'sk_test_audit_not_a_real_key',
            'cashier.webhook.secret' => 'whsec_audit_not_a_real_secret',
        ]);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        Http::preventStrayRequests();
    }

    protected function user(array $attributes = []): User
    {
        return User::factory()->create(array_merge(['password' => self::PASSWORD], $attributes));
    }

    /** Real AES and real RSA envelopes; no fake cryptography. */
    protected function vault(User $user, ?string $kek = null): array
    {
        $kek ??= random_bytes(32);
        $vault = app(CoffreService::class)->creerCoffre($user, ['nom' => 'Coffre audit'], $kek);
        $dataKey = app(CleManagementService::class)->dechiffrerDataKeyCoffre($vault->data_key_encrypted, $kek);

        return [$vault, $dataKey, $kek];
    }

    protected function element(Coffre $vault, string $key, array $data = []): ElementCoffre
    {
        return app(CoffreService::class)->ajouterElement($vault, array_merge([
            'type' => 'login', 'label' => 'Service audit', 'identifiant' => 'audit@example.test',
            'mot_de_passe' => 'vault-secret-value', 'notes' => 'note initiale',
        ], $data), $key);
    }

    protected function unlock(User $user, string $kek, ?string $privateKey = null): void
    {
        static $fixturePrivateKey;
        $fixturePrivateKey ??= app(RsaCryptoService::class)->genererPaireCles()['cle_privee'];
        $privateKey ??= $fixturePrivateKey;
        $this->actingAs($user)->withSession([
            'crypto.kek' => base64_encode($kek), 'crypto.cle_privee' => $privateKey,
        ]);
    }

    protected function share(User $owner, User $recipient, Coffre $vault, string $dataKey, array $attributes = []): array
    {
        $keys = app(RsaCryptoService::class)->genererPaireCles();
        $share = ShareCoffre::create(array_merge([
            'coffre_id' => $vault->id, 'proprietaire_id' => $owner->id,
            'destinataire_id' => $recipient->id, 'permission' => 'lecture',
            'statut' => 'accepte', 'expire_le' => now()->addDay(),
            'data_key_destinataire_encrypted' => app(RsaCryptoService::class)
                ->chiffrerAvecClePublique($dataKey, $keys['cle_publique']),
        ], $attributes));

        return [$share, $keys['cle_privee']];
    }
}
