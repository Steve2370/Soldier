<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shares_coffre', function (Blueprint $table): void {
            $table->json('element_ids')->nullable()->after('accepte_le');
            $table->index(['destinataire_id', 'statut', 'expire_le']);
        });

        Schema::table('invitations_partage', function (Blueprint $table): void {
            $table->json('element_ids')->nullable()->after('traitee_le');
        });
    }

    public function down(): void
    {
        Schema::table('shares_coffre', function (Blueprint $table): void {
            $table->dropIndex(['destinataire_id', 'statut', 'expire_le']);
            $table->dropColumn('element_ids');
        });

        Schema::table('invitations_partage', function (Blueprint $table): void {
            $table->dropColumn('element_ids');
        });
    }
};
