<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_links', function (Blueprint $table) {
            $table->id();
            // O MVP nao usa Soft Delete (docs/03 §10): um link que sobrevivesse
            // a prancheta continuaria acessivel apontando para o nada.
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('board_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_links');
    }
};
