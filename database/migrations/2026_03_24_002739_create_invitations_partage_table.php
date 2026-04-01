<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invitations_partage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coffre_id')
                ->constrained('coffres')
                ->cascadeonDelete();
            $table->string('email_destinataire');
            $table->string('token_hash', 64)->unique();
            $table->text('data_key_chiffree')->nullable();
            $table->enum('permission', ['lecture', 'ecriture'])->default('lecture');
            $table->enum('statut', ['en_attente', 'acceptee', 'refusee', 'expiree'])
                ->default('en_attente');
            $table->timestamp('expire_le');
            $table->timestamp('traitee_le')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations_partage');
    }
};
