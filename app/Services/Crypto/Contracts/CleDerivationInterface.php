<?php

namespace App\Services\Crypto\Contracts;

interface CleDerivationInterface
{
    public function deriver(string $motDePasse, ?string $sel = null): array;
    public function recalculer(string $motDePasse, string $sel, array $parametres): string;
}
