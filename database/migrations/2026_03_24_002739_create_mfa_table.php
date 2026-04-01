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
        Schema::create('mfa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeonDelete();
            $table->enum('type', ['email', 'totp'])->default('email');
            $table->string('code_hash')->nullable();
            $table->timestamp('code_expire_le')->nullable();
            $table->unsignedTinyInteger('tentatives')->default(0);
            $table->text('totp_secret_chiffre')->nullable();
            $table->json('codes_recuperation')->nullable();
            $table->boolean('actif')->default(false);
            $table->timestamp('active_le')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mfa');
    }
};
