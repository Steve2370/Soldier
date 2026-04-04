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
        Schema::create('shares_coffre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coffre_id')->constrained('coffres')->cascadeonDelete();
            $table->foreignId('proprietaire_id')->constrained('users')->cascadeonDelete();
            $table->foreignId('destinataire_id')->constrained('users')->cascadeonDelete();
            $table->text('data_key_destinataire_encrypted');
            $table->enum('permission', ['lecture', 'ecriture'])->default('lecture');
            $table->timestamp('expire_le')->nullable();
            $table->enum('statut', ['en_entente', 'accepte', 'refuse', 'revoque'])->default('en_entente');
            $table->timestamp('accepte_le')->nullable();
            $table->unique(['coffre_id', 'destinataire_id']);
            $table->timestamps();
            $table->index(['proprietaire_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shares_coffre');
    }
};
