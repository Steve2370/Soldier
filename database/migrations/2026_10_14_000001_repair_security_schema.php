<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->softDeletes());
        }
        if (! Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', fn (Blueprint $table) => $table->boolean('is_admin')->default(false));
        }

        // Nullable additions preserve incomplete historical rows without making
        // them valid tokens. Never invent a token hash or an owner for old rows.
        $columns = [
            'tokenable_type' => fn (Blueprint $t) => $t->string('tokenable_type')->nullable(),
            'tokenable_id' => fn (Blueprint $t) => $t->unsignedBigInteger('tokenable_id')->nullable(),
            'name' => fn (Blueprint $t) => $t->string('name')->nullable(),
            'token' => fn (Blueprint $t) => $t->string('token', 64)->nullable(),
            'abilities' => fn (Blueprint $t) => $t->text('abilities')->nullable(),
            'last_used_at' => fn (Blueprint $t) => $t->timestamp('last_used_at')->nullable(),
            'expires_at' => fn (Blueprint $t) => $t->timestamp('expires_at')->nullable(),
        ];
        foreach ($columns as $column => $definition) {
            if (! Schema::hasColumn('personal_access_tokens', $column)) {
                Schema::table('personal_access_tokens', $definition);
            }
        }
        if (! Schema::hasIndex('personal_access_tokens', ['tokenable_type', 'tokenable_id'])) {
            Schema::table('personal_access_tokens', fn (Blueprint $t) => $t->index(['tokenable_type', 'tokenable_id']));
        }
        if (! Schema::hasIndex('personal_access_tokens', ['token'], 'unique')) {
            Schema::table('personal_access_tokens', fn (Blueprint $t) => $t->unique('token'));
        }

        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action')->index();
                $table->text('description')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->nullable()->index();
            });
        }
        if (! Schema::hasColumn('mfa', 'last_totp_step')) {
            Schema::table('mfa', fn (Blueprint $t) => $t->unsignedBigInteger('last_totp_step')->nullable());
        }
        foreach (['shares_coffre', 'invitations_partage'] as $table) {
            if (! Schema::hasColumn($table, 'family_group_id')) {
                Schema::table($table, fn (Blueprint $t) => $t->foreignId('family_group_id')->nullable()->index());
            }
        }
    }

    public function down(): void
    {
        // Repair can adopt pre-existing columns containing real keys/accounts.
        // Removing them on rollback would destroy data that this migration did
        // not create. Use a forward migration after a backup instead.
        throw new RuntimeException('Security schema repair is forward-only; restore a verified backup or use a forward migration.');
    }
};
