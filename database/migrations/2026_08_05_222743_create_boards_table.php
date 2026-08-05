<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            // O canvas fica em JSON para permitir evoluir o editor sem criar
            // uma tabela para cada tipo de elemento (docs/03 §6.1).
            $table->json('canvas_data');
            $table->string('visibility')->default('private');
            $table->timestamps();

            $table->index('user_id');
            $table->index('visibility');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
};
