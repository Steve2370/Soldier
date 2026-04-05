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
        Schema::create('passkeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeonDelete();
            $table->string('nom', 100);
            $table->string('credential_id')->unique();
            $table->text('cle_publique');
            $table->unsignedBigInteger('compteur')->default(0);
            $table->string('type_authenticator', 20)->nullable();
            $table->integer('algorithme_cose')->nullable();
            $table->timestamp('derniere_utilisation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passkeys');
    }
};
