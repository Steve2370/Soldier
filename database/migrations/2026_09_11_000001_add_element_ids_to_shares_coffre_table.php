<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('shares_coffre', 'element_ids')) {
            Schema::table('shares_coffre', function (Blueprint $table): void {
                $table->json('element_ids')->nullable()->after('accepte_le');
            });
        }

        if (! Schema::hasIndex('shares_coffre', ['destinataire_id', 'statut', 'expire_le'])) {
            Schema::table('shares_coffre', function (Blueprint $table): void {
                $table->index(['destinataire_id', 'statut', 'expire_le']);
            });
        }

        if (! Schema::hasColumn('invitations_partage', 'element_ids')) {
            Schema::table('invitations_partage', function (Blueprint $table): void {
                $table->json('element_ids')->nullable()->after('traitee_le');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('shares_coffre', ['destinataire_id', 'statut', 'expire_le'])) {
            Schema::table('shares_coffre', function (Blueprint $table): void {
                $table->dropIndex(['destinataire_id', 'statut', 'expire_le']);
            });
        }

        if (Schema::hasColumn('shares_coffre', 'element_ids')) {
            Schema::table('shares_coffre', function (Blueprint $table): void {
                $table->dropColumn('element_ids');
            });
        }

        if (Schema::hasColumn('invitations_partage', 'element_ids')) {
            Schema::table('invitations_partage', function (Blueprint $table): void {
                $table->dropColumn('element_ids');
            });
        }
    }
};
