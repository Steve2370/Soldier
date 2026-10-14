<?php

namespace Tests\Feature\Audit;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Logs\ActivityLogService;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\AuditTestCase;

class SchemaContractTest extends AuditTestCase
{
    public static function requiredSchema(): array
    {
        return [
            'soft-deleted users' => ['users', 'deleted_at'],
            'administrator flag' => ['users', 'is_admin'],
            'activity journal' => ['activity_logs', 'action'],
            'share selection' => ['shares_coffre', 'element_ids'],
            'invitation selection' => ['invitations_partage', 'element_ids'],
            'family vault' => ['family_groups', 'coffre_id'],
            'sessions' => ['sessions', 'user_id'],
            'subscription status' => ['subscriptions', 'stripe_status'],
            'encrypted keys' => ['cles_user', 'encrypted_kek'],
            'Sanctum token owner' => ['personal_access_tokens', 'tokenable_id'],
            'Sanctum token hash' => ['personal_access_tokens', 'token'],
        ];
    }

    #[DataProvider('requiredSchema')]
    public function test_fresh_installation_contains_required_schema(string $table, string $column): void
    {
        self::assertTrue(Schema::hasColumn($table, $column), "Missing migration: {$table}.{$column}");
    }

    public function test_fresh_installation_can_reload_a_registered_user(): void
    {
        $user = $this->user();
        self::assertSame($user->id, User::findOrFail($user->id)->id);
    }

    public function test_activity_logging_really_persists_an_event(): void
    {
        ActivityLogService::log('audit_probe', 'Synthetic audit event');
        self::assertTrue(Schema::hasTable('activity_logs'), 'Logging errors are swallowed; no journal table exists.');
        self::assertSame(1, ActivityLog::where('action', 'audit_probe')->count());
    }

    public function test_share_migration_is_idempotent(): void
    {
        $migration = require database_path('migrations/2026_09_11_000001_add_element_ids_to_shares_coffre_table.php');
        $migration->up();
        $migration->up();
        self::assertTrue(Schema::hasColumn('shares_coffre', 'element_ids'));
        self::assertTrue(Schema::hasColumn('invitations_partage', 'element_ids'));
        self::assertTrue(Schema::hasIndex('shares_coffre', ['destinataire_id', 'statut', 'expire_le']));
    }
}
