<?php

namespace Tests\Support;

use App\Services\Auth\UserRegistrationService;

/**
 * Historical name retained for the audit suites. No schema is added here:
 * every scenario now runs exclusively against the application's migrations.
 */
abstract class LegacySchemaAuditTestCase extends AuditTestCase
{
    protected function registeredUser(array $attributes = []): array
    {
        $result = app(UserRegistrationService::class)->inscrire([
            'name' => 'Audit User', 'email' => 'audit-'.bin2hex(random_bytes(5)).'@example.test',
            'password' => self::PASSWORD, 'master_password' => self::MASTER,
        ]);
        $result['user']->forceFill(array_merge(['email_verified_at' => now()], $attributes))->save();

        return [$result['user'], $result['cles']];
    }
}
