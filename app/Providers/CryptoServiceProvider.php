<?php

namespace App\Providers;

use App\Services\Crypto\AesEncryptionService;
use App\Services\Crypto\Argon2CleDerivation;
use App\Services\Crypto\Contracts\CleDerivationInterface;
use App\Services\Crypto\Contracts\CryptoAsymmetricInterface;
use App\Services\Crypto\Contracts\EncryptionServiceInterface;
use App\Services\Crypto\RsaCryptoService;
use Illuminate\Support\ServiceProvider;

class CryptoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            EncryptionServiceInterface::class,
            AesEncryptionService::class);

        $this->app->bind(
            CleDerivationInterface::class,
            Argon2CleDerivation::class
        );

        $this->app->bind(
            CryptoAsymmetricInterface::class,
            RsaCryptoService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
