<?php

namespace App\Services\Coffre;

use App\Services\Coffre\Contracts\ElementPayloadInterface;
use InvalidArgumentException;

final readonly class ElementPayload implements ElementPayloadInterface
{
    public function __construct(private array $schemas) {}

    public function build(string $type, array $input, array $previous = []): array
    {
        $schema = $this->schemas[$type] ?? throw new InvalidArgumentException('Unknown vault element type.');
        $payload = [];
        foreach ($schema['fields'] as $stored => $default) {
            $field = $schema['aliases'][$stored] ?? $stored;
            if (! array_key_exists($field, $input)) {
                $payload[$stored] = $previous[$stored] ?? $default;

                continue;
            }
            $value = $input[$field];
            $keep = in_array($stored, $schema['preserve_blank'] ?? [], true)
                && ($value === null || $value === '');
            $payload[$stored] = $keep ? ($previous[$stored] ?? $default) : ($value ?? $default);
        }

        return array_filter($payload, fn ($value) => $value !== null);
    }
}
