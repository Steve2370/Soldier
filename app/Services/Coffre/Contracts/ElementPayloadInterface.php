<?php

namespace App\Services\Coffre\Contracts;

interface ElementPayloadInterface
{
    public function build(string $type, array $input, array $previous = []): array;
}
