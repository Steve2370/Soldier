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
        Schema::create('elements_coffres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coffre_id')->constrained('coffres')->cascadeonDelete();
            $table->enum('type', ['login', 'carte', 'note', 'identite', 'cles', 'autre'])->default('login');
            $table->string('label', 200);
            $table->string('url', 500)->nullable();
            $table->string('favicon_url', 500)->nullable();
            $table->longtext('payload_encrypted');
            $table->string('iv', 24);
            $table->string('auth_tag', 24);
            $table->unsignedTinyInteger('version_schema')->default(1);
            $table->boolean('favori')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['coffre_id', 'type']);
            $table->index(['coffre_id', 'favori']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elements_coffres');
    }
};
