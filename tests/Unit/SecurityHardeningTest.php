<?php

namespace Tests\Unit;

use App\Exceptions\DecryptionException;
use App\Models\ShareCoffre;
use App\Services\Crypto\AesEncryptionService;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    public function test_aes_round_trip_and_strict_envelope_validation(): void
    {
        $service = new AesEncryptionService();
        $key = random_bytes(32);
        $envelope = $service->encrypt('secret', $key);

        self::assertSame('secret', $service->decrypt(
            $envelope['ciphertext'],
            $key,
            $envelope['iv'],
            $envelope['tag'],
        ));

        $this->expectException(DecryptionException::class);
        $service->decrypt($envelope['ciphertext'], $key, 'not-base64', $envelope['tag']);
    }

    public function test_expired_share_is_invalid_and_cannot_write(): void
    {
        $share = new ShareCoffre([
            'statut' => 'accepte',
            'permission' => 'ecriture',
            'expire_le' => now()->subMinute(),
        ]);

        self::assertFalse($share->isValide());
        self::assertFalse($share->peuEcrire());
    }

    public function test_active_read_write_share_uses_the_persisted_permission(): void
    {
        $share = new ShareCoffre([
            'statut' => 'accepte',
            'permission' => 'ecriture',
            'expire_le' => now()->addMinute(),
        ]);

        self::assertTrue($share->isValide());
        self::assertTrue($share->peuEcrire());
    }

    public function test_security_headers_are_added_to_http_responses(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'no-referrer');
    }
}
