<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cles_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('kdf_salt');
            $table->string('kdf_algorithme', 50);
            $table->json('kdf_params');
            $table->json('encrypted_kek');
            $table->text('public_key');
            $table->json('encrypted_private_key');
            $table->json('verification_master_key');
            $table->unsignedTinyInteger('version_schema')->default(1);
            $table->timestamps();
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cles_user');
    }
};
