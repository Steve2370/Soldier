<?php

namespace Tests\Unit;

use App\Exceptions\DecryptionException;
use App\Services\Crypto\AesEncryptionService;
use App\Services\Crypto\Argon2CleDerivation;
use App\Services\Crypto\RsaCryptoService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CryptoBoundaryTest extends TestCase
{
    public static function plaintexts(): array
    {
        return [[''], ['Unicode é 🐎'], ["binary\0\xff"], [str_repeat('secret', 2000)]];
    }

    #[DataProvider('plaintexts')]
    public function test_aes_roundtrip_for_payload_boundaries(string $plaintext): void
    {
        $service = new AesEncryptionService;
        $key = random_bytes(32);
        $encrypted = $service->encrypt($plaintext, $key);
        self::assertSame($plaintext, $service->decrypt($encrypted['ciphertext'], $key, $encrypted['iv'], $encrypted['tag']));
    }

    public static function corruptions(): array
    {
        return [['ciphertext'], ['iv'], ['tag'], ['key']];
    }

    #[DataProvider('corruptions')]
    public function test_aes_authenticates_each_component(string $component): void
    {
        $service = new AesEncryptionService;
        $key = random_bytes(32);
        $encrypted = $service->encrypt('private payload', $key);
        if ($component === 'key') {
            $key[0] = chr(ord($key[0]) ^ 1);
        } else {
            $bytes = base64_decode($encrypted[$component]);
            $bytes[0] = chr(ord($bytes[0]) ^ 1);
            $encrypted[$component] = base64_encode($bytes);
        }
        $this->expectException(DecryptionException::class);
        $service->decrypt($encrypted['ciphertext'], $key, $encrypted['iv'], $encrypted['tag']);
    }

    public function test_aes_generates_distinct_ivs_for_the_same_plaintext(): void
    {
        $service = new AesEncryptionService;
        $key = random_bytes(32);
        self::assertNotSame($service->encrypt('secret', $key)['iv'], $service->encrypt('secret', $key)['iv']);
    }

    public static function invalidKeySizes(): array
    {
        return [[0], [16], [31], [33], [64]];
    }

    #[DataProvider('invalidKeySizes')]
    public function test_aes_rejects_invalid_key_size(int $size): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new AesEncryptionService)->encrypt('secret', str_repeat('k', $size));
    }

    public static function invalidKdfParameters(): array
    {
        return [
            [['memoire' => 1024]], [['memoire' => 268435457]], [['iterations' => 0]],
            [['iterations' => 11]], [['longueur' => 64]], [['version' => '1.2']], [['algorithme' => 'argon2i']],
        ];
    }

    #[DataProvider('invalidKdfParameters')]
    public function test_kdf_rejects_parameters_outside_allowed_bounds(array $changes): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Argon2CleDerivation)->recalculer('password', str_repeat('ab', 16), array_merge([
            'memoire' => 67108864, 'iterations' => 4, 'longueur' => 32,
            'version' => '1.3', 'algorithme' => 'argon2id',
        ], $changes));
    }

    public static function invalidSalts(): array
    {
        return [[''], ['not-hex'], [str_repeat('a', 30)], [str_repeat('a', 34)]];
    }

    #[DataProvider('invalidSalts')]
    public function test_kdf_rejects_malformed_persisted_salt(string $salt): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Argon2CleDerivation)->recalculer('password', $salt, []);
    }

    public function test_kdf_uses_persisted_work_factor(): void
    {
        $service = new Argon2CleDerivation;
        $derived = $service->deriver('password');
        $different = array_merge($derived['parametres'], ['iterations' => 3]);
        self::assertNotSame($derived['cle'], $service->recalculer('password', $derived['sel'], $different));
        self::assertNotSame($derived['cle'], $service->recalculer('wrong-password', $derived['sel'], $derived['parametres']));
    }

    public function test_rsa_rejects_malformed_base64_without_returning_plaintext(): void
    {
        $service = new RsaCryptoService;
        $keys = $service->genererPaireCles();
        $this->expectException(DecryptionException::class);
        $service->decrypterAvecClePrivee('%%%not-base64%%%', $keys['cle_privee']);
    }
}
